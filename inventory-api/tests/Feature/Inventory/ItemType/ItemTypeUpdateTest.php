<?php

use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('updates an item type', function () {
    $itemType = ItemType::factory()->create([
        'code' => 'COMPUTER',
        'name' => 'Computer',
        'tracking_type' => 'none',
    ]);

    $this->patchJson(
        route('item-types.update', $itemType),
        [
            'name' => 'Computer Equipment',
            'tracking_type' => 'serial',
            'is_asset' => true,
            'is_composite' => true,
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item type updated successfully.',
        ])
        ->assertJsonPath('data.name', 'Computer Equipment')
        ->assertJsonPath('data.tracking_type', 'serial')
        ->assertJsonPath('data.is_asset', true)
        ->assertJsonPath('data.is_composite', true);

    $this->assertDatabaseHas('item_types', [
        'id' => $itemType->id,
        'name' => 'Computer Equipment',
        'tracking_type' => 'serial',
        'is_asset' => true,
        'is_composite' => true,
    ]);
});

it('allows keeping the existing code', function () {
    $itemType = ItemType::factory()->create([
        'code' => 'COMPUTER',
    ]);

    $this->patchJson(
        route('item-types.update', $itemType),
        [
            'code' => 'COMPUTER',
        ]
    )
        ->assertOk();
});

it('rejects a duplicate code', function () {
    ItemType::factory()->create([
        'code' => 'COMPUTER',
    ]);

    $itemType = ItemType::factory()->create([
        'code' => 'PRINTER',
    ]);

    $this->patchJson(
        route('item-types.update', $itemType),
        [
            'code' => 'COMPUTER',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('rejects an invalid tracking type', function () {
    $itemType = ItemType::factory()->create();

    $this->patchJson(
        route('item-types.update', $itemType),
        [
            'tracking_type' => 'invalid',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tracking_type');
});

it('returns 404 when updating a missing item type', function () {
    $this->patchJson(
        route('item-types.update', 999999),
        [
            'name' => 'Updated',
        ]
    )
        ->assertNotFound();
});

it('rejects guest requests', function () {
    $itemType = ItemType::factory()->create();

    auth()->forgetGuards();

    $this->patchJson(
        route('item-types.update', $itemType),
        [
            'name' => 'Updated',
        ]
    )
        ->assertUnauthorized();
});