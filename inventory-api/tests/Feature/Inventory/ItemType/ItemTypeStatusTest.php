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

it('activates an item type', function () {
    $itemType = ItemType::factory()->create([
        'is_active' => false,
    ]);

    $this->postJson(
        route('item-types.activate', $itemType)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item type activated successfully.',
        ])
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('item_types', [
        'id' => $itemType->id,
        'is_active' => true,
    ]);
});

it('deactivates an item type', function () {
    $itemType = ItemType::factory()->create([
        'is_active' => true,
    ]);

    $this->postJson(
        route('item-types.deactivate', $itemType)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item type deactivated successfully.',
        ])
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('item_types', [
        'id' => $itemType->id,
        'is_active' => false,
    ]);
});

it('returns 404 when activating a missing item type', function () {
    $this->postJson(
        route('item-types.activate', 999999)
    )
        ->assertNotFound();
});

it('returns 404 when deactivating a missing item type', function () {
    $this->postJson(
        route('item-types.deactivate', 999999)
    )
        ->assertNotFound();
});

it('rejects guest activation', function () {
    $itemType = ItemType::factory()->create([
        'is_active' => false,
    ]);

    auth()->forgetGuards();

    $this->postJson(
        route('item-types.activate', $itemType)
    )
        ->assertUnauthorized();
});

it('rejects guest deactivation', function () {
    $itemType = ItemType::factory()->create([
        'is_active' => true,
    ]);

    auth()->forgetGuards();

    $this->postJson(
        route('item-types.deactivate', $itemType)
    )
        ->assertUnauthorized();
});