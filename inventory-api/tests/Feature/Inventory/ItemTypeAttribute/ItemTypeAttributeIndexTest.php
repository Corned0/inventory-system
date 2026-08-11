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

it('returns attributes assigned to an item type', function () {
    $itemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'name' => 'Brand',
    ]);

    $itemTypeAttribute = ItemTypeAttribute::factory()
        ->for($itemType, 'itemType')
        ->for($attribute, 'attributeDefinition')
        ->create([
            'is_required' => true,
            'sort_order' => 1,
        ]);

    $this->getJson(
        route('item-type-attributes.index', $itemType)
    )
        ->assertOk()
        ->assertJsonPath('data.0.id', $itemTypeAttribute->id)
        ->assertJsonPath(
            'data.0.attribute_definition.id',
            $attribute->id
        )
        ->assertJsonPath(
            'data.0.attribute_definition.code',
            'brand'
        )
        ->assertJsonPath('data.0.is_required', true)
        ->assertJsonPath('data.0.sort_order', 1)
        ->assertJsonPath('meta.total', 1);
});

it('does not return attributes assigned to another item type', function () {
    $itemType = ItemType::factory()->create();
    $otherItemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()->create();

    $itemTypeAttribute = ItemTypeAttribute::factory()
        ->for($itemType, 'itemType')
        ->for($attribute, 'attributeDefinition')
        ->create();

    ItemTypeAttribute::factory()
        ->for($otherItemType, 'itemType')
        ->for(AttributeDefinition::factory(), 'attributeDefinition')
        ->create();

    $this->getJson(
        route('item-type-attributes.index', $itemType)
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $itemTypeAttribute->id);
});