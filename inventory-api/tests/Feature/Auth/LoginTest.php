<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;

uses(RefreshDatabase::class);

it('logs in an active user', function () {
    $user = User::factory()->create([
        'username' => 'jdoe',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $response = $this->postJson(route('auth.login'), [
        'username' => 'jdoe',
        'password' => 'password123',
    ]);

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Login successful.',
        ])
        ->assertJsonStructure([
            'data' => [
                'user',
                'token',
            ],
            'message',
        ]);

    expect($response->json('data.user.id'))->toBe($user->id)
        ->and($response->json('data.token'))->toBeString()
        ->and($response->json('data.token'))->not->toBeEmpty();

    expect(PersonalAccessToken::count())->toBe(1);
});

it('rejects invalid username or password', function () {
    User::factory()->create([
        'username' => 'jdoe',
        'password' => 'password123',
        'is_active' => true,
    ]);

    $response = $this->postJson(route('auth.login'), [
        'username' => 'jdoe',
        'password' => 'wrong-password',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid username or password.',
        ]);

    expect(PersonalAccessToken::count())->toBe(0);
});

it('rejects inactive users', function () {
    User::factory()->create([
        'username' => 'jdoe',
        'password' => 'password123',
        'is_active' => false,
    ]);

    $response = $this->postJson(route('auth.login'), [
        'username' => 'jdoe',
        'password' => 'password123',
    ]);

    $response
        ->assertUnauthorized()
        ->assertJson([
            'message' => 'Invalid username or password.',
        ]);

    expect(PersonalAccessToken::count())->toBe(0);
});

it('requires username', function () {
    $response = $this->postJson(route('auth.login'), [
        'password' => 'password123',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

it('requires password', function () {
    $response = $this->postJson(route('auth.login'), [
        'username' => 'jdoe',
    ]);

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('does not authenticate a nonexistent user', function () {
    $response = $this->postJson(route('auth.login'), [
        'username' => 'does-not-exist',
        'password' => 'password123',
    ]);

    $response->assertUnauthorized();

    expect(PersonalAccessToken::count())->toBe(0);
});