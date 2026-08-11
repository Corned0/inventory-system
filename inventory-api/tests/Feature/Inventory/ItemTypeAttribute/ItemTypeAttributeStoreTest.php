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

it('assigns an attribute to an item type', function () {
    $itemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()->create([
        'code' => 'brand',
    ]);

    $this->postJson(
        route('item-type-attributes.store', $itemType),
        [
            'attribute_definition_id' => $attribute->id,
            'is_required' => true,
            'sort_order' => 1,
        ]
    )
        ->assertCreated()
        ->assertJson([
            'message' => 'Attribute assigned successfully.',
        ])
        ->assertJsonPath(
            'data.attribute_definition.id',
            $attribute->id
        )
        ->assertJsonPath('data.is_required', true)
        ->assertJsonPath('data.sort_order', 1);

    $this->assertDatabaseHas('item_type_attributes', [
        'item_type_id' => $itemType->id,
        'attribute_definition_id' => $attribute->id,
        'is_required' => true,
        'sort_order' => 1,
    ]);
});

it('rejects assigning the same attribute twice', function () {
    $itemType = ItemType::factory()->create();
    $attribute = AttributeDefinition::factory()->create();

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($attribute, 'attributeDefinition')
        ->create();

    $this->postJson(
        route('item-type-attributes.store', $itemType),
        [
            'attribute_definition_id' => $attribute->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('attribute_definition_id');
});

it('requires an existing attribute definition', function () {
    $itemType = ItemType::factory()->create();

    $this->postJson(
        route('item-type-attributes.store', $itemType),
        [
            'attribute_definition_id' => 999999,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('attribute_definition_id');
});