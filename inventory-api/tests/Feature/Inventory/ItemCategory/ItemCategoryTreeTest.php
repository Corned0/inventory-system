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

it('returns the category tree', function () {
    $it = ItemCategory::factory()->create([
        'code' => 'IT',
        'name' => 'IT Equipment',
        'parent_id' => null,
    ]);

    $computers = ItemCategory::factory()
        ->childOf($it)
        ->create([
            'code' => 'COMPUTERS',
            'name' => 'Computers',
        ]);

    ItemCategory::factory()
        ->childOf($it)
        ->create([
            'code' => 'PRINTERS',
            'name' => 'Printers',
        ]);

    $this->getJson(
        route('categories.tree')
    )
        ->assertOk()
        ->assertJsonPath('data.0.id', $it->id)
        ->assertJsonPath('data.0.children.0.id', $computers->id);
});

it('supports multiple levels of nesting', function () {
    $root = ItemCategory::factory()->create([
        'name' => 'IT Equipment',
        'parent_id' => null,
    ]);

    $levelOne = ItemCategory::factory()
        ->childOf($root)
        ->create([
            'name' => 'Computers',
        ]);

    $levelTwo = ItemCategory::factory()
        ->childOf($levelOne)
        ->create([
            'name' => 'Laptops',
        ]);

    $levelThree = ItemCategory::factory()
        ->childOf($levelTwo)
        ->create([
            'name' => 'Business Laptops',
        ]);

    $this->getJson(
        route('categories.tree')
    )
        ->assertOk()
        ->assertJsonPath('data.0.id', $root->id)
        ->assertJsonPath(
            'data.0.children.0.id',
            $levelOne->id
        )
        ->assertJsonPath(
            'data.0.children.0.children.0.id',
            $levelTwo->id
        )
        ->assertJsonPath(
            'data.0.children.0.children.0.children.0.id',
            $levelThree->id
        );
});

it('returns only root categories at the top level', function () {
    $root = ItemCategory::factory()->create([
        'parent_id' => null,
    ]);

    ItemCategory::factory()
        ->childOf($root)
        ->create();

    $response = $this->getJson(
        route('categories.tree')
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data');
});