<?php

use App\Models\AttributeDefinition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an attribute definition', function () {
    $payload = [
        'code' => 'brand',
        'name' => 'Brand',
        'data_type' => 'select',
        'description' => 'Equipment manufacturer.',
        'is_required' => true,
        'is_active' => true,
        'sort_order' => 1,
    ];

    $response = $this->postJson(
        route('attributes.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Attribute created successfully.',
        ])
        ->assertJsonPath('data.code', 'brand')
        ->assertJsonPath('data.name', 'Brand')
        ->assertJsonPath('data.data_type', 'select')
        ->assertJsonPath('data.is_required', true)
        ->assertJsonPath('data.is_active', true)
        ->assertJsonPath('data.sort_order', 1);

    $this->assertDatabaseHas('attribute_definitions', [
        'code' => 'brand',
        'name' => 'Brand',
        'data_type' => 'select',
        'is_required' => true,
        'is_active' => true,
        'sort_order' => 1,
    ]);
});

it('requires code', function () {
    $this->postJson(
        route('attributes.store'),
        [
            'name' => 'Brand',
            'data_type' => 'text',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('requires name', function () {
    $this->postJson(
        route('attributes.store'),
        [
            'code' => 'brand',
            'data_type' => 'text',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires a valid data type', function () {
    $this->postJson(
        route('attributes.store'),
        [
            'code' => 'brand',
            'name' => 'Brand',
            'data_type' => 'invalid',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('data_type');
});

it('rejects duplicate attribute codes', function () {
    AttributeDefinition::factory()->create([
        'code' => 'brand',
    ]);

    $this->postJson(
        route('attributes.store'),
        [
            'code' => 'brand',
            'name' => 'Brand',
            'data_type' => 'text',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});