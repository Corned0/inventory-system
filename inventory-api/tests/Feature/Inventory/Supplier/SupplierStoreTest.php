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

it('creates a supplier', function () {
    $this->postJson(
        route('suppliers.store'),
        [
            'supplier_code' => 'SUP-001',
            'name' => 'Dell Technologies',
            'contact_person' => 'John Doe',
            'email' => 'john@dell.example',
            'phone' => '09171234567',
            'address' => 'Makati City',
            'tax_number' => '123-456-789',
            'is_active' => true,
        ]
    )
        ->assertCreated()
        ->assertJson([
            'message' => 'Supplier created successfully.',
        ])
        ->assertJsonPath('data.supplier_code', 'SUP-001')
        ->assertJsonPath('data.name', 'Dell Technologies');

    expect(Supplier::query()
        ->where('supplier_code', 'SUP-001')
        ->exists()
    )->toBeTrue();
});

it('requires supplier code and name', function () {
    $this->postJson(
        route('suppliers.store'),
        []
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors([
            'supplier_code',
            'name',
        ]);
});

it('rejects a duplicate supplier code', function () {
    Supplier::factory()->create([
        'supplier_code' => 'SUP-001',
    ]);

    $this->postJson(
        route('suppliers.store'),
        [
            'supplier_code' => 'SUP-001',
            'name' => 'Another Supplier',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('supplier_code');
});

it('accepts nullable supplier contact fields', function () {
    $this->postJson(
        route('suppliers.store'),
        [
            'supplier_code' => 'SUP-001',
            'name' => 'Dell Technologies',
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.supplier_code', 'SUP-001');
});