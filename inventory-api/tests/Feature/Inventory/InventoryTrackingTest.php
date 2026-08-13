<?php

use App\Enums\InventorySerialStatus;
use App\Models\InventoryLot;
use App\Models\InventorySerial;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates an inventory lot for an item', function () {
    $item = Item::factory()->create();

    $lot = InventoryLot::factory()
        ->for($item)
        ->create();

    expect($lot->item_id)
        ->toBe($item->id);

    expect($lot->lot_number)
        ->not->toBeEmpty();
});

it('creates an inventory serial for an item', function () {
    $item = Item::factory()->create();

    $serial = InventorySerial::factory()
        ->for($item)
        ->create();

    expect($serial->item_id)
        ->toBe($item->id);

    expect($serial->status)
        ->toBe(InventorySerialStatus::Available);
});

it('casts inventory serial status to the enum', function () {
    $serial = InventorySerial::factory()->create([
        'status' => InventorySerialStatus::Repair,
    ]);

    $serial->refresh();

    expect($serial->status)
        ->toBe(InventorySerialStatus::Repair);
});

it('belongs to an item', function () {
    $item = Item::factory()->create();

    $lot = InventoryLot::factory()
        ->for($item)
        ->create();

    $serial = InventorySerial::factory()
        ->for($item)
        ->create();

    expect($lot->item)
        ->toBeInstanceOf(Item::class)
        ->id->toBe($item->id);

    expect($serial->item)
        ->toBeInstanceOf(Item::class)
        ->id->toBe($item->id);
});

it('returns the item inventory lots and serials', function () {
    $item = Item::factory()->create();

    InventoryLot::factory()
        ->count(2)
        ->for($item)
        ->create();

    InventorySerial::factory()
        ->count(3)
        ->for($item)
        ->create();

    expect($item->inventoryLots)
        ->toHaveCount(2);

    expect($item->inventorySerials)
        ->toHaveCount(3);
});