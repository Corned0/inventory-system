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

it('returns a paginated list of warehouses', function () {
    Warehouse::factory()->count(25)->create();

    $response = $this->getJson(
        route('warehouses.index'),
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'description',
                    'address',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ])
        ->assertJsonPath('meta.per_page', 20)
        ->assertJsonPath('meta.total', 25)
        ->assertJsonCount(20, 'data');
});

it('creates a warehouse', function () {
    $payload = [
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
        'description' => 'Main inventory warehouse',
        'address' => 'Main Office',
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('warehouses.store'),
        $payload,
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.code', 'WH-MAIN')
        ->assertJsonPath('data.name', 'Main Warehouse')
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('warehouses', [
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ]);
});

it('uses the default active status when is_active is omitted', function () {
    $payload = [
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ];

    $response = $this->postJson(
        route('warehouses.store'),
        $payload,
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('warehouses', [
        'code' => 'WH-MAIN',
        'is_active' => true,
    ]);
});

it('rejects a duplicate warehouse code', function () {
    Warehouse::factory()->create([
        'code' => 'WH-MAIN',
    ]);

    $response = $this->postJson(
        route('warehouses.store'),
        [
            'code' => 'WH-MAIN',
            'name' => 'Another Warehouse',
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code',
        ]);
});

it('requires code and name when creating a warehouse', function () {
    $response = $this->postJson(
        route('warehouses.store'),
        [],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code',
            'name',
        ]);
});

it('returns a warehouse', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ]);

    $response = $this->getJson(
        route('warehouses.show', $warehouse),
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $warehouse->id)
        ->assertJsonPath('data.code', 'WH-MAIN')
        ->assertJsonPath('data.name', 'Main Warehouse');
});

it('updates a warehouse', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ]);

    $response = $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'name' => 'Updated Warehouse',
            'is_active' => false,
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $warehouse->id)
        ->assertJsonPath('data.name', 'Updated Warehouse')
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'name' => 'Updated Warehouse',
        'is_active' => false,
    ]);
});

it('allows a warehouse to keep its existing code when updating', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
    ]);

    $response = $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'code' => 'WH-MAIN',
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.code', 'WH-MAIN');
});

it('rejects a warehouse code belonging to another warehouse', function () {
    Warehouse::factory()->create([
        'code' => 'WH-MAIN',
    ]);

    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-SECONDARY',
    ]);

    $response = $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'code' => 'WH-MAIN',
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code',
        ]);
});