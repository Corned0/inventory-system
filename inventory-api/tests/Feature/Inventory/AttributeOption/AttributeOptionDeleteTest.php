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

it('deletes an attribute option', function () {
    $option = AttributeOption::factory()->create();

    $this->deleteJson(
        route('attribute-options.destroy', $option)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Attribute option deleted successfully.',
        ]);

    $this->assertDatabaseMissing('attribute_options', [
        'id' => $option->id,
    ]);
});

it('returns 404 for a missing attribute option', function () {
    $this->deleteJson(
        route('attribute-options.destroy', 999999)
    )
        ->assertNotFound();
});