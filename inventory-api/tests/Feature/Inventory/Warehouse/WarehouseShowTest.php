<?php

use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns a warehouse', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ]);

    $this->getJson(
        route('warehouses.show', $warehouse)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $warehouse->id)
        ->assertJsonPath('data.code', 'WH-MAIN')
        ->assertJsonPath('data.name', 'Main Warehouse');
});

it('returns 404 for a missing warehouse', function () {
    $this->getJson(
        route('warehouses.show', 999999)
    )
        ->assertNotFound();
});