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

it('returns an item type', function () {
    $itemType = ItemType::factory()->create([
        'code' => 'COMPUTER',
        'name' => 'Computer Equipment',
        'tracking_type' => 'serial',
        'is_asset' => true,
        'is_composite' => true,
    ]);

    $this->getJson(
        route('item-types.show', $itemType)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $itemType->id)
        ->assertJsonPath('data.code', 'COMPUTER')
        ->assertJsonPath('data.name', 'Computer Equipment')
        ->assertJsonPath('data.tracking_type', 'serial')
        ->assertJsonPath('data.is_asset', true)
        ->assertJsonPath('data.is_composite', true);
});

it('returns 404 for a missing item type', function () {
    $this->getJson(
        route('item-types.show', 999999)
    )
        ->assertNotFound();
});

it('rejects guest requests', function () {
    $itemType = ItemType::factory()->create();

    auth()->forgetGuards();

    $this->getJson(
        route('item-types.show', $itemType)
    )
        ->assertUnauthorized();
});