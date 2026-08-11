<?php

use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('creates an attribute option', function () {
    $attribute = AttributeDefinition::factory()->create();

    $payload = [
        'value' => 'dell',
        'label' => 'Dell',
        'sort_order' => 1,
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('attribute-options.store', [
            'attribute' => $attribute,
        ]),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Attribute option created successfully.',
        ])
        ->assertJsonPath('data.value', 'dell')
        ->assertJsonPath('data.label', 'Dell')
        ->assertJsonPath('data.sort_order', 1)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('attribute_options', [
        'attribute_definition_id' => $attribute->id,
        'value' => 'dell',
        'label' => 'Dell',
    ]);
});

it('requires value', function () {
    $attribute = AttributeDefinition::factory()->create();

    $this->postJson(
        route('attribute-options.store', [
            'attribute' => $attribute,
        ]),
        [
            'label' => 'Dell',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('value');
});

it('requires label', function () {
    $attribute = AttributeDefinition::factory()->create();

    $this->postJson(
        route('attribute-options.store', [
            'attribute' => $attribute,
        ]),
        [
            'value' => 'dell',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('label');
});

it('rejects duplicate value within the same attribute', function () {
    $attribute = AttributeDefinition::factory()->create();

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'dell',
        ]);

    $this->postJson(
        route('attribute-options.store', [
            'attribute' => $attribute,
        ]),
        [
            'value' => 'dell',
            'label' => 'Dell',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('value');
});

it('allows the same value under a different attribute', function () {
    $attribute = AttributeDefinition::factory()->create();
    $otherAttribute = AttributeDefinition::factory()->create();

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'dell',
        ]);

    $this->postJson(
        route('attribute-options.store', [
            'attribute' => $otherAttribute,
        ]),
        [
            'value' => 'dell',
            'label' => 'Dell',
        ]
    )
        ->assertCreated();
});