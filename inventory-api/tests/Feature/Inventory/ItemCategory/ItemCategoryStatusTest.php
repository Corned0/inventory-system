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

it('activates a category', function () {
    $category = ItemCategory::factory()
        ->inactive()
        ->create();

    $this->postJson(
        route('categories.activate', $category)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Category activated successfully.',
        ]);

    $this->assertDatabaseHas('item_categories', [
        'id' => $category->id,
        'is_active' => true,
    ]);
});

it('deactivates a category', function () {
    $category = ItemCategory::factory()->create([
        'is_active' => true,
    ]);

    $this->postJson(
        route('categories.deactivate', $category)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Category deactivated successfully.',
        ]);

    $this->assertDatabaseHas('item_categories', [
        'id' => $category->id,
        'is_active' => false,
    ]);
});