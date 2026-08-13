<?php

use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('updates a supplier', function () {
    $supplier = Supplier::factory()->create([
        'supplier_code' => 'SUP-001',
        'name' => 'Dell Technologies',
    ]);

    $this->patchJson(
        route('suppliers.update', $supplier),
        [
            'name' => 'Dell Technologies Philippines',
            'contact_person' => 'Jane Doe',
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Supplier updated successfully.',
        ])
        ->assertJsonPath(
            'data.name',
            'Dell Technologies Philippines'
        )
        ->assertJsonPath(
            'data.contact_person',
            'Jane Doe'
        );
});

it('allows keeping the existing supplier code', function () {
    $supplier = Supplier::factory()->create([
        'supplier_code' => 'SUP-001',
        'name' => 'Dell Technologies',
    ]);

    $this->patchJson(
        route('suppliers.update', $supplier),
        [
            'supplier_code' => 'SUP-001',
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.supplier_code', 'SUP-001');
});

it('rejects a duplicate supplier code', function () {
    Supplier::factory()->create([
        'supplier_code' => 'SUP-001',
    ]);

    $supplier = Supplier::factory()->create([
        'supplier_code' => 'SUP-002',
    ]);

    $this->patchJson(
        route('suppliers.update', $supplier),
        [
            'supplier_code' => 'SUP-001',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('supplier_code');
});

it('returns 404 when updating a missing supplier', function () {
    $this->patchJson(
        route('suppliers.update', 999999),
        [
            'name' => 'Updated Supplier',
        ]
    )
        ->assertNotFound();
});