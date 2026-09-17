<?php

use App\Enums\InventorySerialStatus;
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

it('updates serial number', function () {
    $serial = InventorySerial::factory()->create([
        'serial_number' => 'SN-001',
    ]);

    $this->patchJson(
        route('inventory-serials.update', $serial),
        [
            'serial_number' => 'SN-002',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.serial_number', 'SN-002');
});

it('prevents changing the serial item', function () {
    $serial = InventorySerial::factory()->create();

    $item = Item::factory()->create();

    $this->patchJson(
        route('inventory-serials.update', $serial),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('allows keeping existing serial number', function () {
    $serial = InventorySerial::factory()->create([
        'serial_number' => 'SN-001',
    ]);

    $this->patchJson(
        route('inventory-serials.update', $serial),
        [
            'serial_number' => 'SN-001',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.serial_number', 'SN-001');
});

it('rejects duplicate serial number on update', function () {
    $serialA = InventorySerial::factory()->create([
        'serial_number' => 'SN-001',
    ]);

    $serialB = InventorySerial::factory()->create([
        'serial_number' => 'SN-002',
    ]);

    $this->patchJson(
        route('inventory-serials.update', $serialB),
        [
            'serial_number' => $serialA->serial_number,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

it('rejects status update', function () {
    $serial = InventorySerial::factory()->create([
        'status' => InventorySerialStatus::Available,
    ]);

    $this->patchJson(
        route('inventory-serials.update', $serial),
        [
            'status' => InventorySerialStatus::Issued->value,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');

    expect($serial->refresh()->status)
        ->toBe(InventorySerialStatus::Available);
});