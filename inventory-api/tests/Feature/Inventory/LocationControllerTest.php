<?php

use App\Models\Location;
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

it('returns a paginated list of locations', function () {
    $warehouse = Warehouse::factory()->create();

    Location::factory()
        ->count(25)
        ->create([
            'warehouse_id' => $warehouse->id,
        ]);

    $response = $this->getJson(
        route('locations.index'),
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'warehouse_id',
                    'parent_id',
                    'code',
                    'name',
                    'location_type',
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

it('creates a root location', function () {
    $warehouse = Warehouse::factory()->create();

    $payload = [
        'warehouse_id' => $warehouse->id,
        'code' => 'BUILDING-A',
        'name' => 'Building A',
        'location_type' => 'building',
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('locations.store'),
        $payload,
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.warehouse_id', $warehouse->id)
        ->assertJsonPath('data.parent_id', null)
        ->assertJsonPath('data.code', 'BUILDING-A')
        ->assertJsonPath('data.location_type', 'building')
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('locations', [
        'warehouse_id' => $warehouse->id,
        'parent_id' => null,
        'code' => 'BUILDING-A',
    ]);
});

it('creates a child location', function () {
    $warehouse = Warehouse::factory()->create();

    $building = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => null,
        'code' => 'BUILDING-A',
        'location_type' => 'building',
    ]);

    $response = $this->postJson(
        route('locations.store'),
        [
            'warehouse_id' => $warehouse->id,
            'parent_id' => $building->id,
            'code' => 'ROOM-101',
            'name' => 'Room 101',
            'location_type' => 'room',
        ],
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.parent_id', $building->id)
        ->assertJsonPath('data.location_type', 'room');

    $this->assertDatabaseHas('locations', [
        'warehouse_id' => $warehouse->id,
        'parent_id' => $building->id,
        'code' => 'ROOM-101',
    ]);
});

it('uses the default active status when is_active is omitted', function () {
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('locations.store'),
        [
            'warehouse_id' => $warehouse->id,
            'code' => 'BUILDING-A',
            'name' => 'Building A',
            'location_type' => 'building',
        ],
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('locations', [
        'warehouse_id' => $warehouse->id,
        'code' => 'BUILDING-A',
        'is_active' => true,
    ]);
});

it('rejects a parent from another warehouse', function () {
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    $parent = Location::factory()->create([
        'warehouse_id' => $warehouseA->id,
        'code' => 'BUILDING-A',
        'location_type' => 'building',
    ]);

    $response = $this->postJson(
        route('locations.store'),
        [
            'warehouse_id' => $warehouseB->id,
            'parent_id' => $parent->id,
            'code' => 'ROOM-101',
            'name' => 'Room 101',
            'location_type' => 'room',
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'parent_id',
        ]);
});

it('rejects a duplicate code within the same warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'BUILDING-A',
    ]);

    $response = $this->postJson(
        route('locations.store'),
        [
            'warehouse_id' => $warehouse->id,
            'code' => 'BUILDING-A',
            'name' => 'Another Building',
            'location_type' => 'building',
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'code',
        ]);
});

it('allows the same code in different warehouses', function () {
    $warehouseA = Warehouse::factory()->create();
    $warehouseB = Warehouse::factory()->create();

    Location::factory()->create([
        'warehouse_id' => $warehouseA->id,
        'code' => 'BUILDING-A',
    ]);

    $response = $this->postJson(
        route('locations.store'),
        [
            'warehouse_id' => $warehouseB->id,
            'code' => 'BUILDING-A',
            'name' => 'Building A',
            'location_type' => 'building',
        ],
    );

    $response->assertCreated();

    $this->assertDatabaseHas('locations', [
        'warehouse_id' => $warehouseB->id,
        'code' => 'BUILDING-A',
    ]);
});

it('returns a location', function () {
    $location = Location::factory()->create();

    $response = $this->getJson(
        route('locations.show', $location),
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $location->id)
        ->assertJsonPath('data.code', $location->code)
        ->assertJsonPath('data.name', $location->name);
});

it('updates a location', function () {
    $location = Location::factory()->create([
        'code' => 'ROOM-101',
        'name' => 'Room 101',
        'location_type' => 'room',
    ]);

    $response = $this->patchJson(
        route('locations.update', $location),
        [
            'name' => 'Room 101 Updated',
            'is_active' => false,
        ],
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $location->id,
        )
        ->assertJsonPath(
            'data.name',
            'Room 101 Updated',
        )
        ->assertJsonPath(
            'data.is_active',
            false,
        );

    $this->assertDatabaseHas('locations', [
        'id' => $location->id,
        'name' => 'Room 101 Updated',
        'is_active' => false,
    ]);
});

it('rejects a location from becoming its own parent', function () {
    $location = Location::factory()->create();

    $response = $this->patchJson(
        route('locations.update', $location),
        [
            'parent_id' => $location->id,
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'parent_id',
        ]);
});

it('rejects a circular hierarchy', function () {
    $warehouse = Warehouse::factory()->create();

    $building = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'code' => 'BUILDING-A',
        'location_type' => 'building',
    ]);

    $room = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => $building->id,
        'code' => 'ROOM-101',
        'location_type' => 'room',
    ]);

    $rack = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => $room->id,
        'code' => 'RACK-A',
        'location_type' => 'rack',
    ]);

    $response = $this->patchJson(
        route('locations.update', $building),
        [
            'parent_id' => $rack->id,
        ],
    );

    $response
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'parent_id',
        ]);
});

it('returns the warehouse location tree', function () {
    $warehouse = Warehouse::factory()->create();

    $building = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => null,
        'code' => 'BUILDING-A',
        'name' => 'Building A',
        'location_type' => 'building',
    ]);

    $room = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => $building->id,
        'code' => 'ROOM-101',
        'name' => 'Room 101',
        'location_type' => 'room',
    ]);

    $rack = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
        'parent_id' => $room->id,
        'code' => 'RACK-A',
        'name' => 'Rack A',
        'location_type' => 'rack',
    ]);

    $response = $this->getJson(
        route(
            'warehouses.locations.tree',
            $warehouse,
        ),
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.0.id',
            $building->id,
        )
        ->assertJsonPath(
            'data.0.children.0.id',
            $room->id,
        )
        ->assertJsonPath(
            'data.0.children.0.children.0.id',
            $rack->id,
        );
});