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

it('returns paginated attribute options', function () {
    $attribute = AttributeDefinition::factory()->create();

    AttributeOption::factory()
        ->count(3)
        ->for($attribute, 'attributeDefinition')
        ->create();

    $response = $this->getJson(
        route('attribute-options.index', [
            'attribute' => $attribute,
        ])
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'value',
                    'label',
                    'sort_order',
                    'is_active',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ])
        ->assertJsonPath('meta.total', 3);
});

it('only returns options belonging to the requested attribute', function () {
    $attribute = AttributeDefinition::factory()->create();
    $otherAttribute = AttributeDefinition::factory()->create();

    $option = AttributeOption::factory()
        ->for($attribute, 'attributeDefinition')
        ->create();

    AttributeOption::factory()
        ->for($otherAttribute, 'attributeDefinition')
        ->create();

    $response = $this->getJson(
        route('attribute-options.index', [
            'attribute' => $attribute,
        ])
    );

    $response
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $option->id);
});