<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns a single user', function () {
    $user = User::factory()->create();

    $response = $this->getJson(route('users.show', $user));

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $user->id)
        ->assertJsonPath('data.email', $user->email);
});

it('returns 404 when user does not exist', function () {
    $response = $this->getJson(route('users.show', 99999));

    $response->assertNotFound();
});