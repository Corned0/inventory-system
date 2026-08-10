<?php

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

it('updates a category', function () {
    $category = ItemCategory::factory()->create([
        'code' => 'IT',
        'name' => 'IT Equipment',
    ]);

    $response = $this->patchJson(
        route('categories.update', $category),
        [
            'name' => 'Information Technology',
        ]
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Category updated successfully.',
        ]);

    $this->assertDatabaseHas('item_categories', [
        'id' => $category->id,
        'name' => 'Information Technology',
    ]);
});

it('changes a category parent', function () {
    $parent = ItemCategory::factory()->create();

    $category = ItemCategory::factory()->create([
        'parent_id' => null,
    ]);

    $this->patchJson(
        route('categories.update', $category),
        [
            'parent_id' => $parent->id,
        ]
    )
        ->assertOk();

    $this->assertDatabaseHas('item_categories', [
        'id' => $category->id,
        'parent_id' => $parent->id,
    ]);
});

it('prevents a category from being its own parent', function () {
    $category = ItemCategory::factory()->create();

    $this->patchJson(
        route('categories.update', $category),
        [
            'parent_id' => $category->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('parent_id');
});

it('rejects a descendant as parent', function () {
    $root = ItemCategory::factory()->create();

    $parent = ItemCategory::factory()
        ->childOf($root)
        ->create();

    $child = ItemCategory::factory()
        ->childOf($parent)
        ->create();

    $this->patchJson(
        route('categories.update', $root),
        [
            'parent_id' => $child->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('parent_id');
});

it('allows moving a category under an unrelated category', function () {
    $category = ItemCategory::factory()->create();
    $newParent = ItemCategory::factory()->create();

    $this->patchJson(
        route('categories.update', $category),
        [
            'parent_id' => $newParent->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.parent_id', $newParent->id);
});

it('rejects duplicate category codes', function () {
    ItemCategory::factory()->create([
        'code' => 'IT',
    ]);

    $category = ItemCategory::factory()->create([
        'code' => 'OFFICE',
    ]);

    $this->patchJson(
        route('categories.update', $category),
        [
            'code' => 'IT',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});
