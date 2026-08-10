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

it('creates a root category', function () {
    $payload = [
        'code' => 'IT',
        'name' => 'IT Equipment',
        'description' => 'Information technology equipment.',
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('categories.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Category created successfully.',
        ]);

    $this->assertDatabaseHas('item_categories', [
        'code' => 'IT',
        'name' => 'IT Equipment',
        'parent_id' => null,
        'is_active' => true,
    ]);
});

it('creates a child category', function () {
    $parent = ItemCategory::factory()->create();

    $payload = [
        'parent_id' => $parent->id,
        'code' => 'COMPUTERS',
        'name' => 'Computers',
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('categories.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.parent_id', $parent->id);

    $this->assertDatabaseHas('item_categories', [
        'parent_id' => $parent->id,
        'code' => 'COMPUTERS',
    ]);
});

it('requires code', function () {
    $this->postJson(
        route('categories.store'),
        [
            'name' => 'IT Equipment',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('requires name', function () {
    $this->postJson(
        route('categories.store'),
        [
            'code' => 'IT',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires a valid parent category', function () {
    $this->postJson(
        route('categories.store'),
        [
            'parent_id' => 999999,
            'code' => 'COMPUTERS',
            'name' => 'Computers',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('parent_id');
});

it('requires a unique code', function () {
    ItemCategory::factory()->create([
        'code' => 'IT',
    ]);

    $this->postJson(
        route('categories.store'),
        [
            'code' => 'IT',
            'name' => 'Another Category',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});