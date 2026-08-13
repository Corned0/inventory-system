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

it('creates an inventory lot', function () {
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('inventory-lots.store'),
        [
            'item_id' => $item->id,
            'lot_number' => 'LOT-001',
            'manufactured_date' => '2026-01-15',
            'expiration_date' => '2028-01-15',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.item_id', $item->id)
        ->assertJsonPath('data.lot_number', 'LOT-001')
        ->assertJsonPath('data.manufactured_date', '2026-01-15')
        ->assertJsonPath('data.expiration_date', '2028-01-15');

    expect(InventoryLot::query()->count())->toBe(1);
});

it('requires an item', function () {
    $this->postJson(
        route('inventory-lots.store'),
        [
            'lot_number' => 'LOT-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('rejects a nonexistent item', function () {
    $this->postJson(
        route('inventory-lots.store'),
        [
            'item_id' => 999999,
            'lot_number' => 'LOT-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('requires a lot number', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-lots.store'),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lot_number');
});

it('rejects a duplicate lot number', function () {
    $item = Item::factory()->create();

    InventoryLot::factory()->create([
        'item_id' => $item->id,
        'lot_number' => 'LOT-001',
    ]);

    $this->postJson(
        route('inventory-lots.store'),
        [
            'item_id' => $item->id,
            'lot_number' => 'LOT-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('lot_number');
});

it('accepts optional manufacturing and expiration dates', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-lots.store'),
        [
            'item_id' => $item->id,
            'lot_number' => 'LOT-001',
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.item_id', $item->id)
        ->assertJsonPath('data.lot_number', 'LOT-001');
});