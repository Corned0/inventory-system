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

it('returns an attribute option', function () {
    $option = AttributeOption::factory()->create([
        'value' => 'dell',
        'label' => 'Dell',
    ]);

    $this->getJson(
        route('attribute-options.show', $option)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $option->id)
        ->assertJsonPath('data.value', 'dell')
        ->assertJsonPath('data.label', 'Dell');
});

it('returns 404 for a missing attribute option', function () {
    $this->getJson(
        route('attribute-options.show', 999999)
    )
        ->assertNotFound();
});