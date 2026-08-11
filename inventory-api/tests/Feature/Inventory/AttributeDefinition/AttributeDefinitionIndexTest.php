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

it('returns paginated attribute definitions', function () {
    AttributeDefinition::factory()
        ->count(25)
        ->create();

    $response = $this->getJson(
        route('attributes.index')
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'data_type',
                    'description',
                    'is_required',
                    'is_active',
                    'sort_order',
                    'validation_rules',
                    'default_value',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ])
        ->assertJsonPath('meta.total', 25);
});