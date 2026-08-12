<?php

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemType;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an item', function () {
    $category = ItemCategory::factory()->create();
    $itemType = ItemType::factory()->create();
    $unit = UnitOfMeasure::factory()->create();

    $response = $this->postJson(
        route('items.store'),
        [
            'name' => 'Dell Precision 3680',
            'description' => 'Workstation',
            'category_id' => $category->id,
            'item_type_id' => $itemType->id,
            'unit_of_measure_id' => $unit->id,
            'barcode' => '123456789',
            'reorder_level' => 1,
            'reorder_quantity' => 5,
        ]
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Item created successfully.',
        ])
        ->assertJsonPath('data.name', 'Dell Precision 3680')
        ->assertJsonPath('data.item_code', fn ($value) =>
            preg_match('/^ITM-\d{6}$/', $value) === 1
        );

    $this->assertDatabaseHas('items', [
        'name' => 'Dell Precision 3680',
        'category_id' => $category->id,
        'item_type_id' => $itemType->id,
        'unit_of_measure_id' => $unit->id,
    ]);
});

it('requires an item name', function () {
    $this->postJson(
        route('items.store'),
        []
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires a valid item type', function () {
    $this->postJson(
        route('items.store'),
        [
            'name' => 'Computer',
            'item_type_id' => 999999,
            'category_id' => ItemCategory::factory()->create()->id,
            'unit_of_measure_id' => UnitOfMeasure::factory()->create()->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_type_id');
});