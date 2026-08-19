<?php

use App\Models\Receiving;
use App\Models\Supplier;
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

it('updates a draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
        'remarks' => 'Original',
    ]);

    $response = $this->patchJson(
        route('receivings.update', $receiving),
        [
            'remarks' => 'Updated remarks',
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.remarks',
            'Updated remarks'
        );
});

it('updates the supplier', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $supplier = Supplier::factory()->create();

    $response = $this->patchJson(
        route('receivings.update', $receiving),
        [
            'supplier_id' => $supplier->id,
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.supplier_id',
            $supplier->id
        );
});

it('updates the warehouse', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'draft',
    ]);

    $warehouse = Warehouse::factory()->create();

    $response = $this->patchJson(
        route('receivings.update', $receiving),
        [
            'warehouse_id' => $warehouse->id,
        ]
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.warehouse_id',
            $warehouse->id
        );
});

it('does not allow updating a non-draft receiving', function () {
    $receiving = Receiving::factory()->create([
        'status' => 'received',
    ]);

    $this->patchJson(
        route('receivings.update', $receiving),
        [
            'remarks' => 'Should fail',
        ]
    )
        ->assertStatus(409);
});