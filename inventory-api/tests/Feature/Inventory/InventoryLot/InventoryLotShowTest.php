<?php

use App\Models\InventoryLot;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns an inventory lot', function () {
    $lot = InventoryLot::factory()->create([
        'lot_number' => 'LOT-001',
        'manufactured_date' => '2026-01-15',
        'expiration_date' => '2028-01-15',
    ]);

    $this->getJson(
        route('inventory-lots.show', $lot)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $lot->id)
        ->assertJsonPath('data.item_id', $lot->item_id)
        ->assertJsonPath('data.lot_number', 'LOT-001')
        ->assertJsonPath('data.manufactured_date', '2026-01-15')
        ->assertJsonPath('data.expiration_date', '2028-01-15');
});

it('includes the item', function () {
    $lot = InventoryLot::factory()->create();

    $this->getJson(
        route('inventory-lots.show', $lot)
    )
        ->assertOk()
        ->assertJsonPath('data.item.id', $lot->item_id);
});

it('returns 404 for a missing inventory lot', function () {
    $this->getJson(
        route('inventory-lots.show', 999999)
    )
        ->assertNotFound();
});