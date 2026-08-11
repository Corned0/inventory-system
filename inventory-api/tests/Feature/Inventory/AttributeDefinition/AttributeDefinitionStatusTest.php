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

it('activates an attribute definition', function () {
    $attribute = AttributeDefinition::factory()
        ->inactive()
        ->create();

    $this->postJson(
        route('attributes.activate', $attribute)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute activated successfully.',
        ])
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('attribute_definitions', [
        'id' => $attribute->id,
        'is_active' => true,
    ]);
});

it('deactivates an attribute definition', function () {
    $attribute = AttributeDefinition::factory()->create();

    $this->postJson(
        route('attributes.deactivate', $attribute)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute deactivated successfully.',
        ])
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('attribute_definitions', [
        'id' => $attribute->id,
        'is_active' => false,
    ]);
});