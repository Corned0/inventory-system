<?php

use App\Models\InventorySerial;
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

it('index returns serials', function () {
    $item = Item::factory()->create();

    InventorySerial::factory()
        ->count(3)
        ->for($item)
        ->create();

    $this->getJson(route('inventory-serials.index'))
        ->assertOk()
        ->assertJsonCount(3, 'data');
});

it('index includes item', function () {
    $item = Item::factory()->create([
        'name' => 'HP ProDesk',
    ]);

    $serial = InventorySerial::factory()
        ->for($item)
        ->create([
            'serial_number' => 'SN-001',
        ]);

    $this->getJson(route('inventory-serials.index'))
        ->assertOk()
        ->assertJsonPath('data.0.id', $serial->id)
        ->assertJsonPath('data.0.item.id', $item->id)
        ->assertJsonPath('data.0.item.name', 'HP ProDesk');
});