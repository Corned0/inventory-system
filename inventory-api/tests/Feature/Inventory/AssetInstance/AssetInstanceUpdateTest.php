<?php

use App\Enums\AssetInstanceStatus;
use App\Models\AssetInstance;
use App\Models\Item;
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

it('updates the serial number', function () {
    $assetInstance = AssetInstance::factory()->create([
        'serial_number' => 'OLD123456',
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'serial_number' => 'NEW123456',
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.serial_number',
            'NEW123456'
        );
});

it('updates the item', function () {
    $assetInstance = AssetInstance::factory()->create();

    $item = Item::factory()->create();

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.item.id',
            $item->id
        );
});

it('allows keeping the existing serial number', function () {
    $assetInstance = AssetInstance::factory()->create([
        'serial_number' => 'ABC123456',
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.serial_number',
            'ABC123456'
        );
});

it('rejects a duplicate serial number on update', function () {
    $serialA = AssetInstance::factory()->create([
        'serial_number' => 'ABC123456',
    ]);

    $serialB = AssetInstance::factory()->create([
        'serial_number' => 'DEF123456',
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $serialB
        ),
        [
            'serial_number' => $serialA->serial_number,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'serial_number'
        );
});

it('updates the status', function () {
    $assetInstance = AssetInstance::factory()->create([
        'status' => AssetInstanceStatus::Available,
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'status' => AssetInstanceStatus::Issued->value,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            AssetInstanceStatus::Issued->value
        );
});

it('updates the warehouse', function () {
    $assetInstance = AssetInstance::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'current_warehouse_id' => $warehouse->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.current_warehouse.id',
            $warehouse->id
        );
});

it('updates the location', function () {
    $assetInstance = AssetInstance::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'current_warehouse_id' => $warehouse->id,
            'current_location_id' => $location->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.current_location.id',
            $location->id
        );
});

it('updates acquisition cost', function () {
    $assetInstance = AssetInstance::factory()->create();

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'acquisition_cost' => 125000.50,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.acquisition_cost',
            '125000.5000'
        );
});

it('updates warranty dates', function () {
    $assetInstance = AssetInstance::factory()->create();

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'warranty_start' => '2026-01-01',
            'warranty_end' => '2029-01-01',
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.warranty_start',
            '2026-01-01'
        )
        ->assertJsonPath(
            'data.warranty_end',
            '2029-01-01'
        );
});

it('does not allow changing the asset number', function () {
    $assetInstance = AssetInstance::factory()->create([
        'asset_number' => 'AST-000123',
    ]);

    $this->patchJson(
        route(
            'asset-instances.update',
            $assetInstance
        ),
        [
            'asset_number' => 'AST-999999',
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000123'
        );
});