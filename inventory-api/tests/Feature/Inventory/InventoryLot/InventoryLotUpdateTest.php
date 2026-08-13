<?php

use App\Models\InventoryLot;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('updates a lot number', function () {
    $lot = InventoryLot::factory()->create([
        'lot_number' => 'LOT-001',
    ]);

    $this->patchJson(
        route('inventory-lots.update', $lot),
        [
            'lot_number' => 'LOT-002',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.lot_number', 'LOT-002');
});

it('updates the item', function () {
    $lot = InventoryLot::factory()->create();

    $item = Item::factory()->create();

    $this->patchJson(
        route('inventory-lots.update', $lot),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.item_id', $item->id);
});

it('updates manufacturing and expiration dates', function () {
    $lot = InventoryLot::factory()->create();

    $this->patchJson(
        route('inventory-lots.update', $lot),
        [
            'manufactured_date' => '2026-02-01',
            'expiration_date' => '2029-02-01',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.manufactured_date', '2026-02-01')
        ->assertJsonPath('data.expiration_date', '2029-02-01');
});

it('allows keeping the existing lot number', function () {
    $lot = InventoryLot::factory()->create([
        'lot_number' => 'LOT-001',
    ]);

    $this->patchJson(
        route('inventory-lots.update', $lot),
        [
            'lot_number' => 'LOT-001',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.lot_number', 'LOT-001');
});

it('rejects a duplicate lot number', function () {
    $item = Item::factory()->create();

    $lotA = InventoryLot::factory()->create([
        'item_id' => $item->id,
        'lot_number' => 'LOT-001',
    ]);

    $lotB = InventoryLot::factory()->create([
        'item_id' => $item->id,
        'lot_number' => 'LOT-002',
    ]);

    $this->patchJson(
        route('inventory-lots.update', $lotB),
        [
            'lot_number' => $lotA->lot_number,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lot_number');
});