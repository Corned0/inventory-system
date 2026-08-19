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

it('moves a received receiving to under inspection', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'received',
    ]);

    $this->postJson(
        route('receivings.inspect', $receiving)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            'under_inspection'
        );
});

it('does not allow inspecting a draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $this->postJson(
        route('receivings.inspect', $receiving)
    )
        ->assertStatus(409);
});

it('does not allow inspecting a completed receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'completed',
    ]);

    $this->postJson(
        route('receivings.inspect', $receiving)
    )
        ->assertStatus(409);
});