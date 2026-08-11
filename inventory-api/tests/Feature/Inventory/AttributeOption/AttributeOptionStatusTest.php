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

it('activates an attribute option', function () {
    $option = AttributeOption::factory()->create([
        'is_active' => false,
    ]);

    $this->postJson(
        route('attribute-options.activate', $option)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute option activated successfully.',
        ])
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('attribute_options', [
        'id' => $option->id,
        'is_active' => true,
    ]);
});

it('deactivates an attribute option', function () {
    $option = AttributeOption::factory()->create([
        'is_active' => true,
    ]);

    $this->postJson(
        route('attribute-options.deactivate', $option)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute option deactivated successfully.',
        ])
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('attribute_options', [
        'id' => $option->id,
        'is_active' => false,
    ]);
});