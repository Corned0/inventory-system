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

it('updates a user', function () {

    // Arrange
    $user = User::factory()->create();

    $payload = [
        'name' => 'Updated User',
        'username' => 'updateduser',
        'employee_id' => 200,
        'is_active' => false,
    ];

    // Act
    $response = $this->patchJson(
        route('users.update', $user),
        $payload
    );

    // Assert
    $response
        ->assertOk()
        ->assertJson([
            'message' => 'User updated successfully.',
        ]);

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated User',
        'username' => 'updateduser',
        'employee_id' => 200,
        'is_active' => false,
    ]);
});

it('updates a password', function () {

    $user = User::factory()->create([
        'password' => bcrypt('oldpassword'),
    ]);

    $response = $this->patchJson(
        route('users.update', $user),
        [
            'password' => 'newpassword123',
        ]
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'User updated successfully.',
        ]);

    expect(
        password_verify(
            'newpassword123',
            $user->refresh()->password
        )
    )->toBeTrue();
});

it('allows partial updates', function () {

    $user = User::factory()->create();

    $this->patchJson(
        route('users.update', $user),
        [
            'name' => 'Only Name Changed',
        ]
    )
        ->assertOk();

    expect($user->fresh()->name)
        ->toBe('Only Name Changed');
});

it('requires unique username', function () {

    User::factory()->create([
        'username' => 'existing',
    ]);

    $user = User::factory()->create();

    $this->patchJson(
        route('users.update', $user),
        [
            'username' => 'existing',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

it('allows keeping the current username', function () {

    $user = User::factory()->create([
        'username' => 'john',
    ]);

    $this->patchJson(
        route('users.update', $user),
        [
            'username' => 'john',
        ]
    )
        ->assertOk();
});

it('requires password to be at least 8 characters', function () {

    $user = User::factory()->create();

    $this->patchJson(
        route('users.update', $user),
        [
            'password' => '123',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('returns 404 when updating a missing user', function () {

    $this->patchJson(
        route('users.update', 999999),
        [
            'name' => 'Test',
        ]
    )
        ->assertNotFound();
});

it('rejects guest requests', function () {

    auth()->forgetGuards();

    $this->patchJson(
        route('users.update', 1),
        [
            'name' => 'Test',
        ]
    )
        ->assertUnauthorized();
});