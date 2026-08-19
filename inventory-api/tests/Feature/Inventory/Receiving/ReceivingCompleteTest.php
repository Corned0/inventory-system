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

it('completes an accepted receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'accepted',
    ]);

    $this->postJson(
        route('receivings.complete', $receiving)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            'completed'
        );
});

it('completes a partially accepted receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'partially_accepted',
    ]);

    $this->postJson(
        route('receivings.complete', $receiving)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            'completed'
        );
});

it('does not allow completing a draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $this->postJson(
        route('receivings.complete', $receiving)
    )
        ->assertStatus(409);
});

it('does not allow completing a rejected receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'rejected',
    ]);

    $this->postJson(
        route('receivings.complete', $receiving)
    )
        ->assertStatus(409);
});

it('does not allow completing an already completed receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'completed',
    ]);

    $this->postJson(
        route('receivings.complete', $receiving)
    )
        ->assertStatus(409);
});