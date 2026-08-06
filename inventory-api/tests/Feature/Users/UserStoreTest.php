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

it('creates a user', function () {

    // Arrange
    $payload = [
        'name' => 'John Doe',
        'username' => 'jdoe',
        'employee_id' => 100,
        'password' => 'password123',
        'is_active' => true,
    ];

    // Act
    $response = $this->postJson(route('users.store'), $payload);

    // Assert
    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'User created successfully.',
        ]);

    $this->assertDatabaseHas('users', [
        'username' => 'jdoe',
        'employee_id' => 100,
        'is_active' => true,
    ]);
});

it('requires username', function () {

    $payload = [
        'name' => 'John Doe',
        'employee_id' => 100,
        'password' => 'password123',
        'is_active' => true,
    ];

    $this->postJson(route('users.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

it('requires employee id', function () {

    $payload = [
        'name' => 'John Doe',
        'username' => 'jdoe',
        'password' => 'password123',
        'is_active' => true,
    ];

    $this->postJson(route('users.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('employee_id');
});

it('requires password', function () {

    $payload = [
        'name' => 'John Doe',
        'username' => 'jdoe',
        'employee_id' => 100,
        'is_active' => true,
    ];

    $this->postJson(route('users.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('password');
});

it('requires unique username', function () {

    User::factory()->create([
        'username' => 'existing',
    ]);

    $payload = [
        'name' => 'John Doe',
        'username' => 'existing',
        'employee_id' => 100,
        'password' => 'password123',
        'is_active' => true,
    ];

    $this->postJson(route('users.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('username');
});

it('hashes the password before saving', function () {

    $payload = [
        'name' => 'John Doe',
        'username' => 'jdoe',
        'employee_id' => 100,
        'password' => 'password123',
        'is_active' => true,
    ];

    $this->postJson(route('users.store'), $payload);

    $user = User::where('username', 'jdoe')->first();

    expect(
        password_verify('password123', $user->password)
    )->toBeTrue();
});

it('rejects guest requests', function () {

    auth()->forgetGuards();

    $this->postJson(route('users.store'), [])
        ->assertUnauthorized();
});