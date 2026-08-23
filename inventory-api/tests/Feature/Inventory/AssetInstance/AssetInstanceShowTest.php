<?php

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

it('shows an asset instance', function () {
    $assetInstance = AssetInstance::factory()->create();

    $this->getJson(
        route(
            'asset-instances.show',
            $assetInstance
        )
    )
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $assetInstance->id
        )
        ->assertJsonPath(
            'data.asset_number',
            $assetInstance->asset_number
        )
        ->assertJsonPath(
            'data.serial_number',
            $assetInstance->serial_number
        );
});

it('includes item', function () {
    $item = Item::factory()->create();

    $assetInstance = AssetInstance::factory()->create([
        'item_id' => $item->id,
    ]);

    $this->getJson(
        route(
            'asset-instances.show',
            $assetInstance
        )
    )
        ->assertOk()
        ->assertJsonPath(
            'data.item.id',
            $item->id
        );
});

it('includes warehouse and location', function () {
    $warehouse = Warehouse::factory()->create();

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $assetInstance = AssetInstance::factory()->create([
        'current_warehouse_id' => $warehouse->id,
        'current_location_id' => $location->id,
    ]);

    $this->getJson(
        route(
            'asset-instances.show',
            $assetInstance
        )
    )
        ->assertOk()
        ->assertJsonPath(
            'data.current_warehouse.id',
            $warehouse->id
        )
        ->assertJsonPath(
            'data.current_location.id',
            $location->id
        );
});

it('returns not found for nonexistent asset instance', function () {
    $this->getJson(
        route(
            'asset-instances.show',
            999999
        )
    )
        ->assertNotFound();
});