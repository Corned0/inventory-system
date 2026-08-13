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

it('returns a supplier', function () {
    $supplier = Supplier::factory()->create([
        'supplier_code' => 'SUP-001',
        'name' => 'Dell Technologies',
    ]);

    $this->getJson(
        route('suppliers.show', $supplier)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $supplier->id)
        ->assertJsonPath('data.supplier_code', 'SUP-001')
        ->assertJsonPath('data.name', 'Dell Technologies');
});

it('returns 404 for a missing supplier', function () {
    $this->getJson(
        route('suppliers.show', 999999)
    )
        ->assertNotFound();
});