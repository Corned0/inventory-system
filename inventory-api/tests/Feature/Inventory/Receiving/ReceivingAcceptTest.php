<?php

use App\Models\Receiving;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('accepts an inspected receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'under_inspection',
    ]);

    $this->postJson(
        route('receivings.accept', $receiving)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            'accepted'
        );
});

it('does not allow accepting a draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $this->postJson(
        route('receivings.accept', $receiving)
    )
        ->assertStatus(409);
});

it('does not allow accepting a cancelled receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'cancelled',
    ]);

    $this->postJson(
        route('receivings.accept', $receiving)
    )
        ->assertStatus(409);
});