<?php

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

it('updates an attribute option', function () {
    $option = AttributeOption::factory()->create([
        'value' => 'dell',
        'label' => 'Dell',
    ]);

    $response = $this->patchJson(
        route('attribute-options.update', $option),
        [
            'label' => 'Dell Technologies',
        ]
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute option updated successfully.',
        ])
        ->assertJsonPath('data.label', 'Dell Technologies');

    $this->assertDatabaseHas('attribute_options', [
        'id' => $option->id,
        'value' => 'dell',
        'label' => 'Dell Technologies',
    ]);
});

it('allows keeping the existing value', function () {
    $option = AttributeOption::factory()->create([
        'value' => 'dell',
    ]);

    $this->patchJson(
        route('attribute-options.update', $option),
        [
            'value' => 'dell',
        ]
    )
        ->assertOk();
});

it('rejects a duplicate value within the same attribute', function () {
    $attribute = \App\Models\AttributeDefinition::factory()->create();

    AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'dell',
        ]);

    $option = AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create([
            'value' => 'hp',
        ]);

    $this->patchJson(
        route('attribute-options.update', $option),
        [
            'value' => 'dell',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('value');
});