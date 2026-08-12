<?php

use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates a warehouse', function () {
    $payload = [
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
        'description' => 'Primary inventory warehouse.',
        'address' => '123 Main Street',
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('warehouses.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Warehouse created successfully.',
        ])
        ->assertJsonPath('data.code', 'WH-MAIN')
        ->assertJsonPath('data.name', 'Main Warehouse')
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('warehouses', [
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
        'is_active' => true,
    ]);
});

it('requires a code', function () {
    $this->postJson(
        route('warehouses.store'),
        [
            'name' => 'Main Warehouse',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('requires a name', function () {
    $this->postJson(
        route('warehouses.store'),
        [
            'code' => 'WH-MAIN',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('rejects duplicate warehouse codes', function () {
    Warehouse::factory()->create([
        'code' => 'WH-MAIN',
    ]);

    $this->postJson(
        route('warehouses.store'),
        [
            'code' => 'WH-MAIN',
            'name' => 'Another Warehouse',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('allows nullable description and address', function () {
    $this->postJson(
        route('warehouses.store'),
        [
            'code' => 'WH-MAIN',
            'name' => 'Main Warehouse',
            'description' => null,
            'address' => null,
        ]
    )
        ->assertCreated();

    $this->assertDatabaseHas('warehouses', [
        'code' => 'WH-MAIN',
        'description' => null,
        'address' => null,
    ]);
});