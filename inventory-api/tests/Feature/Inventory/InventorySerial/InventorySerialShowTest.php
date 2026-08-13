<?php

use App\Models\InventorySerial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('show returns serial', function () {
    $serial = InventorySerial::factory()->create([
        'serial_number' => 'SN-001',
    ]);

    $this->getJson(
        route('inventory-serials.show', $serial)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $serial->id)
        ->assertJsonPath('data.item_id', $serial->item_id)
        ->assertJsonPath('data.serial_number', 'SN-001')
        ->assertJsonPath('data.status', 'available');
});

it('show includes item', function () {
    $serial = InventorySerial::factory()->create();

    $this->getJson(
        route('inventory-serials.show', $serial)
    )
        ->assertOk()
        ->assertJsonPath('data.item.id', $serial->item_id);
});

it('returns 404 for a missing serial', function () {
    $this->getJson(
        route('inventory-serials.show', 999999)
    )
        ->assertNotFound();
});