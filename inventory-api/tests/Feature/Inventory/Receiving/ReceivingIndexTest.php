<?php

use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\Item;
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

it('returns receivings', function () {
    Receiving::factory()
        ->count(3)
        ->create();

    $response = $this->getJson(
        route('receivings.index')
    );

    $response
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('includes receiving items', function () {
    $receiving = Receiving::factory()->create();

    $item = Item::factory()->create();

    ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
    ]);

    $response = $this->getJson(
        route('receivings.index')
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.0.items.0.item_id',
            $item->id
        );
});

it('includes supplier', function () {
    $supplier = Supplier::factory()->create();

    Receiving::factory()->create([
        'supplier_id' => $supplier->id,
    ]);

    $response = $this->getJson(
        route('receivings.index')
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.0.supplier_id',
            $supplier->id
        );
});

it('includes warehouse', function () {
    $warehouse = Warehouse::factory()->create();

    Receiving::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $response = $this->getJson(
        route('receivings.index')
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.0.warehouse_id',
            $warehouse->id
        );
});