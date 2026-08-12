<?php

use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
use App\Models\Item;
use App\Models\ItemType;
use App\Models\UnitOfMeasure;
use App\Models\ItemCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an item with dynamic attributes', function () {
    $itemType = ItemType::factory()->create();

    $brand = AttributeDefinition::factory()
        ->select()
        ->required()
        ->create([
            'code' => 'brand',
            'name' => 'Brand',
        ]);

    AttributeOption::factory()
        ->for($brand, 'attributeDefinition')
        ->create([
            'value' => 'dell',
            'label' => 'Dell',
        ]);

    $ram = AttributeDefinition::factory()->create([
        'code' => 'ram',
        'name' => 'RAM',
        'data_type' => 'integer',
    ]);

    $itemType->attributeDefinitions()->attach([
        $brand->id => [
            'is_required' => true,
            'sort_order' => 1,
        ],
        $ram->id => [
            'is_required' => false,
            'sort_order' => 2,
        ],
    ]);

    $response = $this->postJson(
        route('items.store'),
        [
            'name' => 'Dell Precision 3680',
            'item_type_id' => $itemType->id,
            'category_id' => ItemCategory::factory()->create()->id,
            'unit_of_measure_id' => UnitOfMeasure::factory()->create()->id,

            'attributes' => [
                'brand' => 'dell',
                'ram' => 32,
            ],
        ]
    );

    $response->assertCreated();

    $item = Item::query()
        ->where('name', 'Dell Precision 3680')
        ->firstOrFail();

    expect($item->attributeValues)
        ->toHaveCount(2);

    $this->assertDatabaseHas('item_attribute_values', [
        'item_id' => $item->id,
        'attribute_definition_id' => $brand->id,
        'value' => 'dell',
    ]);

    $this->assertDatabaseHas('item_attribute_values', [
        'item_id' => $item->id,
        'attribute_definition_id' => $ram->id,
        'value' => '32',
    ]);
});

it('returns item attributes', function () {
    $itemType = ItemType::factory()->create();

    $brand = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'data_type' => 'text',
    ]);

    $itemType->attributeDefinitions()->attach(
        $brand->id,
        [
            'is_required' => false,
            'sort_order' => 1,
        ]
    );

    $item = Item::factory()->create([
        'item_type_id' => $itemType->id,
    ]);

    $item->attributeValues()->create([
        'attribute_definition_id' => $brand->id,
        'value' => 'Dell',
    ]);

    $this->getJson(
        route('items.attributes', $item)
    )
        ->assertOk()
        ->assertJsonPath('data.brand', 'Dell');
});

it('updates item attributes', function () {
    $itemType = ItemType::factory()->create();

    $brand = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'data_type' => 'text',
    ]);

    $itemType->attributeDefinitions()->attach(
        $brand->id,
        [
            'is_required' => false,
            'sort_order' => 1,
        ]
    );

    $item = Item::factory()->create([
        'item_type_id' => $itemType->id,
    ]);

    $this->patchJson(
        route('items.attributes.update', $item),
        [
            'attributes' => [
                'brand' => 'HP',
            ],
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item attributes updated successfully.',
        ]);

    $this->assertDatabaseHas('item_attribute_values', [
        'item_id' => $item->id,
        'attribute_definition_id' => $brand->id,
        'value' => 'HP',
    ]);
});