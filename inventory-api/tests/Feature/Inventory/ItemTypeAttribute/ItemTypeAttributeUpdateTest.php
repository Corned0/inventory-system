<?php

use App\Models\AttributeDefinition;
use App\Models\ItemType;
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

it('updates an item type attribute', function () {
    $itemTypeAttribute = ItemTypeAttribute::factory()->create([
        'is_required' => false,
        'sort_order' => 1,
    ]);

    $this->patchJson(
        route('item-type-attributes.update', [
            'itemType' => $itemTypeAttribute->itemType,
            'itemTypeAttribute' => $itemTypeAttribute,
        ]),
        [
            'is_required' => true,
            'sort_order' => 5,
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item type attribute updated successfully.',
        ])
        ->assertJsonPath('data.is_required', true)
        ->assertJsonPath('data.sort_order', 5);

    $this->assertDatabaseHas('item_type_attributes', [
        'id' => $itemTypeAttribute->id,
        'is_required' => true,
        'sort_order' => 5,
    ]);
});

it('does not allow changing the attribute definition', function () {
    $itemTypeAttribute = ItemTypeAttribute::factory()->create();

    $newAttribute = AttributeDefinition::factory()->create();

    $this->patchJson(
        route('item-type-attributes.update', [
            'itemType' => $itemTypeAttribute->itemType,
            'itemTypeAttribute' => $itemTypeAttribute,
        ]),
        [
            'attribute_definition_id' => $newAttribute->id,
        ]
    )
        ->assertOk();

    expect($itemTypeAttribute->refresh()->attribute_definition_id)
        ->not->toBe($newAttribute->id);
});

it('returns 404 when the relationship does not belong to the item type', function () {
    $itemTypeAttribute = ItemTypeAttribute::factory()->create();
    $otherItemType = ItemType::factory()->create();

    $this->patchJson(
        route('item-type-attributes.update', [
            'itemType' => $otherItemType,
            'itemTypeAttribute' => $itemTypeAttribute,
        ]),
        [
            'is_required' => true,
        ]
    )
        ->assertNotFound();
});