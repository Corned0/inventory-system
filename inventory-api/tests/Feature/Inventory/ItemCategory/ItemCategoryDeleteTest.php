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

it('deletes a category without children', function () {
    $category = ItemCategory::factory()->create();

    $this->deleteJson(
        route('categories.destroy', $category)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Category deleted successfully.',
        ]);

    $this->assertDatabaseMissing('item_categories', [
        'id' => $category->id,
    ]);
});

it('cannot delete a category with children', function () {
    $parent = ItemCategory::factory()->create();

    ItemCategory::factory()
        ->childOf($parent)
        ->create();

    $this->deleteJson(
        route('categories.destroy', $parent)
    )
        ->assertStatus(409)
        ->assertJson([
            'message' => 'Category cannot be deleted because it has child categories.',
        ]);

    $this->assertDatabaseHas('item_categories', [
        'id' => $parent->id,
    ]);
});

it('returns 404 when deleting a missing category', function () {
    $this->deleteJson(
        route('categories.destroy', 999999)
    )
        ->assertNotFound();
});