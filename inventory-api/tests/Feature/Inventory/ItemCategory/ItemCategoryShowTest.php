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

it('returns a category', function () {
    $category = ItemCategory::factory()->create([
        'code' => 'IT',
        'name' => 'IT Equipment',
    ]);

    $this->getJson(
        route('categories.show', $category)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $category->id)
        ->assertJsonPath('data.code', 'IT')
        ->assertJsonPath('data.name', 'IT Equipment');
});

it('returns a category with its children', function () {
    $parent = ItemCategory::factory()->create();

    $child = ItemCategory::factory()
        ->childOf($parent)
        ->create();

    $this->getJson(
        route('categories.show', $parent)
    )
        ->assertOk()
        ->assertJsonPath('data.children.0.id', $child->id);
});

it('returns 404 for a missing category', function () {
    $this->getJson(
        route('categories.show', 999999)
    )
        ->assertNotFound();
});
