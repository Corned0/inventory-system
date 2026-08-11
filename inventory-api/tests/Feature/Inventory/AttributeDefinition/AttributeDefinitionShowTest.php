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

it('returns an attribute definition', function () {
    $attribute = AttributeDefinition::factory()
        ->select()
        ->create([
            'code' => 'brand',
            'name' => 'Brand',
        ]);

    $this->getJson(
        route('attributes.show', $attribute)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $attribute->id)
        ->assertJsonPath('data.code', 'brand')
        ->assertJsonPath('data.name', 'Brand')
        ->assertJsonPath('data.data_type', 'select');
});

it('returns 404 for a missing attribute definition', function () {
    $this->getJson(
        route('attributes.show', 999999)
    )
        ->assertNotFound();
});