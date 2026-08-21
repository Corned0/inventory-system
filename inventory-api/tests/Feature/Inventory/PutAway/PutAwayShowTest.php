<?php

use App\Enums\PutAwayStatus;
use App\Models\Item;
use App\Models\Location;
use App\Models\PutAway;
use App\Models\PutAwayItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

it('shows a put-away', function () {
    $this->actingAsAdmin();

    $item = Item::factory()->create();

    $supplier = Supplier::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $receiving = Receiving::factory()->create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $receivingItem = ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
    ]);

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $putAway = PutAway::factory()->create([
        'receiving_id' => $receiving->id,
        'warehouse_id' => $warehouse->id,
        'status' => PutAwayStatus::Draft,
    ]);

    PutAwayItem::factory()->create([
        'put_away_id' => $putAway->id,
        'receiving_item_id' => $receivingItem->id,
        'item_id' => $item->id,
        'location_id' => $location->id,
        'quantity' => 10,
    ]);

    $this->getJson(
        route(
            'put-aways.show',
            $putAway
        )
    )
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $putAway->id
        )
        ->assertJsonPath(
            'data.receiving_id',
            $receiving->id
        )
        ->assertJsonPath(
            'data.warehouse_id',
            $warehouse->id
        )
        ->assertJsonPath(
            'data.items.0.item_id',
            $item->id
        )
        ->assertJsonPath(
            'data.items.0.location_id',
            $location->id
        );
});