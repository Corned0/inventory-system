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

it('returns paginated categories', function () {
    ItemCategory::factory()->count(10)->create();

    $response = $this->getJson(
        route('categories.index')
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data',
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
});

it('returns categories with parent information', function () {
    $parent = ItemCategory::factory()->create([
        'code' => 'IT',
        'name' => 'IT Equipment',
    ]);

    $child = ItemCategory::factory()
        ->childOf($parent)
        ->create([
            'code' => 'COMPUTERS',
            'name' => 'Computers',
        ]);

    $response = $this->getJson(
        route('categories.index')
    );

    $response
        ->assertOk()
        ->assertJsonFragment([
            'id' => $child->id,
            'parent_id' => $parent->id,
            'code' => 'COMPUTERS',
            'name' => 'Computers',
        ])
        ->assertJsonFragment([
            'id' => $parent->id,
            'code' => 'IT',
            'name' => 'IT Equipment',
        ]);
});

