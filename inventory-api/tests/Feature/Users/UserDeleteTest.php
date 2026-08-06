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

it('deletes a user', function () {
    $user = User::factory()->create();

    $response = $this->deleteJson(route('users.destroy', $user));

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'User deleted successfully.',
        ]);

    $this->assertDatabaseMissing('users', [
        'id' => $user->id,
    ]);
});

it('returns 404 when deleting a non-existing user', function () {
    $response = $this->deleteJson(route('users.destroy', 999999));

    $response->assertNotFound();
});