<?php

use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
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

it('returns item type metadata', function () {
    $itemType = ItemType::factory()->create([
        'code' => 'COMPUTER',
        'name' => 'Computer Equipment',
    ]);

    $attribute = AttributeDefinition::factory()
        ->select()
        ->create([
            'code' => 'brand',
            'name' => 'Brand',
        ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($attribute, 'attributeDefinition')
        ->create([
            'is_required' => true,
            'sort_order' => 1,
        ]);

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'dell',
            'label' => 'Dell',
            'sort_order' => 1,
            'is_active' => true,
        ]);

    $response = $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.item_type.id', $itemType->id)
        ->assertJsonPath('data.item_type.code', 'COMPUTER')
        ->assertJsonPath('data.item_type.name', 'Computer Equipment')
        ->assertJsonPath('data.attributes.0.id', $attribute->id)
        ->assertJsonPath('data.attributes.0.code', 'brand')
        ->assertJsonPath('data.attributes.0.name', 'Brand')
        ->assertJsonPath('data.attributes.0.data_type', 'select')
        ->assertJsonPath('data.attributes.0.required', true)
        ->assertJsonPath('data.attributes.0.options.0.value', 'dell')
        ->assertJsonPath('data.attributes.0.options.0.label', 'Dell');
});

it('uses item type attribute required setting', function () {
    $itemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()
        ->required()
        ->create([
            'code' => 'brand',
            'name' => 'Brand',
        ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($attribute, 'attributeDefinition')
        ->create([
            'is_required' => false,
        ]);

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonPath('data.attributes.0.required', false);
});

it('returns attributes in sort order', function () {
    $itemType = ItemType::factory()->create();

    $ram = AttributeDefinition::factory()->create([
        'code' => 'ram',
        'name' => 'RAM',
    ]);

    $brand = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'name' => 'Brand',
    ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($ram, 'attributeDefinition')
        ->create([
            'sort_order' => 2,
        ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($brand, 'attributeDefinition')
        ->create([
            'sort_order' => 1,
        ]);

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonPath('data.attributes.0.code', 'brand')
        ->assertJsonPath('data.attributes.1.code', 'ram');
});

it('only returns active attribute definitions', function () {
    $itemType = ItemType::factory()->create();

    $active = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'is_active' => true,
    ]);

    $inactive = AttributeDefinition::factory()->inactive()->create([
        'code' => 'legacy_brand',
    ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($active, 'attributeDefinition')
        ->create();

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($inactive, 'attributeDefinition')
        ->create();

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonCount(1, 'data.attributes')
        ->assertJsonPath('data.attributes.0.code', 'brand');
});

it('only returns active options', function () {
    $itemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()
        ->select()
        ->create([
            'code' => 'brand',
        ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($attribute, 'attributeDefinition')
        ->create();

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'dell',
            'label' => 'Dell',
            'is_active' => true,
        ]);

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'old',
            'label' => 'Old Brand',
            'is_active' => false,
        ]);

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonCount(1, 'data.attributes.0.options')
        ->assertJsonPath(
            'data.attributes.0.options.0.value',
            'dell'
        );
});

it('does not return options for non selectable attributes', function () {
    $itemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()->create([
        'code' => 'ram',
        'name' => 'RAM',
        'data_type' => 'integer',
    ]);

    ItemTypeAttribute::factory()
        ->for($itemType)
        ->for($attribute, 'attributeDefinition')
        ->create();

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create();

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonPath('data.attributes.0.data_type', 'integer')
        ->assertJsonCount(0, 'data.attributes.0.options');
});

it('does not return attributes assigned to another item type', function () {
    $itemType = ItemType::factory()->create();
    $otherItemType = ItemType::factory()->create();

    $attribute = AttributeDefinition::factory()->create([
        'code' => 'brand',
    ]);

    ItemTypeAttribute::factory()
        ->for($otherItemType)
        ->for($attribute, 'attributeDefinition')
        ->create();

    $this->getJson(
        route('item-types.attributes.metadata', $itemType)
    )
        ->assertOk()
        ->assertJsonCount(0, 'data.attributes');
});

it('returns 404 for a missing item type', function () {
    $this->getJson(
        route('item-types.attributes.metadata', 999999)
    )
        ->assertNotFound();
});