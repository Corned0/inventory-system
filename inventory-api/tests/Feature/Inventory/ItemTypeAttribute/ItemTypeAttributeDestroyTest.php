<?php

use App\Models\ItemTypeAttribute;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('removes an attribute from an item type', function () {
    $itemTypeAttribute = ItemTypeAttribute::factory()->create();

    $this->deleteJson(
        route('item-type-attributes.destroy', [
            'itemType' => $itemTypeAttribute->itemType,
            'itemTypeAttribute' => $itemTypeAttribute,
        ])
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute removed successfully.',
        ]);

    $this->assertDatabaseMissing('item_type_attributes', [
        'id' => $itemTypeAttribute->id,
    ]);
});

it('returns 404 when removing an attribute from the wrong item type', function () {
    $itemTypeAttribute = ItemTypeAttribute::factory()->create();

    $otherItemType = \App\Models\ItemType::factory()->create();

    $this->deleteJson(
        route('item-type-attributes.destroy', [
            'itemType' => $otherItemType,
            'itemTypeAttribute' => $itemTypeAttribute,
        ])
    )
        ->assertNotFound();
});