<?php

use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an item type', function () {
    $payload = [
        'code' => 'COMPUTER',
        'name' => 'Computer Equipment',
        'description' => 'Computer equipment.',
        'tracking_type' => 'serial',
        'is_asset' => true,
        'is_composite' => true,
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('item-types.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Item type created successfully.',
        ])
        ->assertJsonPath('data.code', 'COMPUTER')
        ->assertJsonPath('data.tracking_type', 'serial')
        ->assertJsonPath('data.is_asset', true)
        ->assertJsonPath('data.is_composite', true);

    $this->assertDatabaseHas('item_types', [
        'code' => 'COMPUTER',
        'name' => 'Computer Equipment',
        'tracking_type' => 'serial',
        'is_asset' => true,
        'is_composite' => true,
        'is_active' => true,
    ]);
});

it('requires code', function () {
    $this->postJson(
        route('item-types.store'),
        [
            'name' => 'Computer Equipment',
            'tracking_type' => 'serial',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('requires name', function () {
    $this->postJson(
        route('item-types.store'),
        [
            'code' => 'COMPUTER',
            'tracking_type' => 'serial',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires tracking type', function () {
    $this->postJson(
        route('item-types.store'),
        [
            'code' => 'COMPUTER',
            'name' => 'Computer Equipment',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tracking_type');
});

it('accepts none tracking type', function () {
    $this->postJson(route('item-types.store'), [
        'code' => 'OFFICE_SUPPLY',
        'name' => 'Office Supply',
        'tracking_type' => 'none',
    ])
        ->assertCreated();
});

it('accepts lot tracking type', function () {
    $this->postJson(route('item-types.store'), [
        'code' => 'FOOD',
        'name' => 'Food',
        'tracking_type' => 'lot',
    ])
        ->assertCreated();
});

it('accepts serial tracking type', function () {
    $this->postJson(route('item-types.store'), [
        'code' => 'COMPUTER',
        'name' => 'Computer',
        'tracking_type' => 'serial',
    ])
        ->assertCreated();
});

it('rejects an invalid tracking type', function () {
    $this->postJson(route('item-types.store'), [
        'code' => 'COMPUTER',
        'name' => 'Computer',
        'tracking_type' => 'invalid',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('tracking_type');
});

it('requires a unique code', function () {
    ItemType::factory()->create([
        'code' => 'COMPUTER',
    ]);

    $this->postJson(route('item-types.store'), [
        'code' => 'COMPUTER',
        'name' => 'Another Computer',
        'tracking_type' => 'serial',
    ])
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('rejects guest requests', function () {
    auth()->forgetGuards();

    $this->postJson(route('item-types.store'), [])
        ->assertUnauthorized();
});