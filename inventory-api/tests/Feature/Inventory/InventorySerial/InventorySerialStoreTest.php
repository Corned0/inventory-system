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

it('creates serial', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'SN-001',
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.item_id', $item->id)
        ->assertJsonPath('data.serial_number', 'SN-001');
});

it('defaults status to available', function () {
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'SN-001',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.status',
            InventorySerialStatus::Available->value
        );

    expect(
        InventorySerial::query()
            ->first()
            ->status
    )->toBe(InventorySerialStatus::Available);
});

it('requires item', function () {
    $this->postJson(
        route('inventory-serials.store'),
        [
            'serial_number' => 'SN-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('rejects nonexistent item', function () {
    $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => 999999,
            'serial_number' => 'SN-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('requires serial number', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

it('rejects duplicate serial number', function () {
    $item = Item::factory()->create();

    InventorySerial::factory()->create([
        'item_id' => $item->id,
        'serial_number' => 'SN-001',
    ]);

    $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'SN-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

it('rejects status during creation', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-serials.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'SN-001',
            'status' => InventorySerialStatus::Disposed->value,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});