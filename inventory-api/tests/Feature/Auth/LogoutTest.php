<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('logs out the authenticated user', function () {
    $user = User::factory()->create();

    $token = $user->createToken('inventory-api');

    expect(PersonalAccessToken::count())->toBe(1);

    $response = $this
        ->withToken($token->plainTextToken)
        ->postJson(route('auth.logout'));

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Logged out successfully.',
        ]);

    expect(
        PersonalAccessToken::find($token->accessToken->id)
    )->toBeNull();
});

it('rejects guest logout requests', function () {
    $this->postJson(route('auth.logout'))
        ->assertUnauthorized();
});