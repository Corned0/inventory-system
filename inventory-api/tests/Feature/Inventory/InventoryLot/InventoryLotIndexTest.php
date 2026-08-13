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

it('returns inventory lots', function () {
    $item = Item::factory()->create();

    $lots = InventoryLot::factory()
        ->count(3)
        ->for($item)
        ->create();

    $this->getJson(route('inventory-lots.index'))
        ->assertOk()
        ->assertJsonCount(3, 'data')
        ->assertJsonPath('data.0.item_id', $lots->first()->item_id);
});

it('includes the item', function () {
    $item = Item::factory()->create([
        'name' => 'HP ProDesk',
    ]);

    $lot = InventoryLot::factory()
        ->for($item)
        ->create([
            'lot_number' => 'LOT-001',
        ]);

    $this->getJson(route('inventory-lots.index'))
        ->assertOk()
        ->assertJsonPath('data.0.id', $lot->id)
        ->assertJsonPath('data.0.item.id', $item->id)
        ->assertJsonPath('data.0.item.name', 'HP ProDesk');
});

it('returns pagination metadata', function () {
    InventoryLot::factory()
        ->count(3)
        ->create();

    $this->getJson(route('inventory-lots.index'))
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
});