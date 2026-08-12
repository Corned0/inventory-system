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

it('updates a warehouse', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
        'description' => 'Old description',
    ]);

    $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'name' => 'Updated Warehouse',
            'description' => 'Updated description',
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Warehouse updated successfully.',
        ])
        ->assertJsonPath('data.name', 'Updated Warehouse')
        ->assertJsonPath('data.description', 'Updated description');

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'name' => 'Updated Warehouse',
        'description' => 'Updated description',
    ]);
});

it('allows keeping the existing code', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
    ]);

    $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'code' => 'WH-MAIN',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.code', 'WH-MAIN');
});

it('rejects a duplicate warehouse code', function () {
    Warehouse::factory()->create([
        'code' => 'WH-MAIN',
    ]);

    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-SECONDARY',
    ]);

    $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'code' => 'WH-MAIN',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('allows updating only the name', function () {
    $warehouse = Warehouse::factory()->create([
        'code' => 'WH-MAIN',
        'name' => 'Main Warehouse',
        'address' => 'Original Address',
    ]);

    $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'name' => 'Updated Warehouse',
        ]
    )
        ->assertOk();

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'code' => 'WH-MAIN',
        'name' => 'Updated Warehouse',
        'address' => 'Original Address',
    ]);
});

it('can deactivate a warehouse through update', function () {
    $warehouse = Warehouse::factory()->create([
        'is_active' => true,
    ]);

    $this->patchJson(
        route('warehouses.update', $warehouse),
        [
            'is_active' => false,
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.is_active', false);

    $this->assertDatabaseHas('warehouses', [
        'id' => $warehouse->id,
        'is_active' => false,
    ]);
});