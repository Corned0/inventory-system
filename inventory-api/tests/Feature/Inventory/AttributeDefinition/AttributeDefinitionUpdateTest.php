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

it('updates an attribute definition', function () {
    $attribute = AttributeDefinition::factory()->create([
        'code' => 'brand',
        'name' => 'Brand',
    ]);

    $this->patchJson(
        route('attributes.update', $attribute),
        [
            'name' => 'Manufacturer',
            'description' => 'Equipment manufacturer.',
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute updated successfully.',
        ]);

    $this->assertDatabaseHas('attribute_definitions', [
        'id' => $attribute->id,
        'name' => 'Manufacturer',
        'description' => 'Equipment manufacturer.',
    ]);
});

it('allows keeping the existing code', function () {
    $attribute = AttributeDefinition::factory()->create([
        'code' => 'brand',
    ]);

    $this->patchJson(
        route('attributes.update', $attribute),
        [
            'code' => 'brand',
        ]
    )
        ->assertOk();
});

it('rejects a duplicate code', function () {
    AttributeDefinition::factory()->create([
        'code' => 'brand',
    ]);

    $attribute = AttributeDefinition::factory()->create([
        'code' => 'model',
    ]);

    $this->patchJson(
        route('attributes.update', $attribute),
        [
            'code' => 'brand',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});