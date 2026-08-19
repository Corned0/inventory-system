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

it('rejects an inspected receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'under_inspection',
    ]);

    $this->postJson(
        route('receivings.reject', $receiving)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            'rejected'
        );
});

it('does not allow rejecting a draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $this->postJson(
        route('receivings.reject', $receiving)
    )
        ->assertStatus(409);
});

it('does not allow rejecting a completed receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'completed',
    ]);

    $this->postJson(
        route('receivings.reject', $receiving)
    )
        ->assertStatus(409);
});