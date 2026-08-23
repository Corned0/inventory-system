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

it('returns asset instances', function () {
    AssetInstance::factory()
        ->count(3)
        ->create();

    $this->getJson(
        route('asset-instances.index')
    )
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('includes item', function () {
    $item = Item::factory()->create();

    AssetInstance::factory()->create([
        'item_id' => $item->id,
    ]);

    $this->getJson(
        route('asset-instances.index')
    )
        ->assertOk()
        ->assertJsonPath(
            'data.0.item.id',
            $item->id
        );
});

it('includes warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    AssetInstance::factory()->create([
        'current_warehouse_id' => $warehouse->id,
    ]);

    $this->getJson(
        route('asset-instances.index')
    )
        ->assertOk()
        ->assertJsonPath(
            'data.0.current_warehouse.id',
            $warehouse->id
        );
});

it('includes location', function () {
    $warehouse = Warehouse::factory()->create();

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    AssetInstance::factory()->create([
        'current_warehouse_id' => $warehouse->id,
        'current_location_id' => $location->id,
    ]);

    $this->getJson(
        route('asset-instances.index')
    )
        ->assertOk()
        ->assertJsonPath(
            'data.0.current_location.id',
            $location->id
        );
});