<?php

use App\Models\InventoryTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns an inventory transaction', function () {
    $transaction = InventoryTransaction::factory()->create([
        'transaction_type' => 'receipt',
        'quantity' => 10,
        'unit_cost' => 1500,
        'remarks' => 'Initial receipt',
    ]);

    $this->getJson(
        route('inventory-transactions.show', $transaction)
    )
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                'id',
                'transaction_number',
                'transaction_type',
                'item_id',
                'warehouse_id',
                'location_id',
                'lot_id',
                'serial_id',
                'quantity',
                'unit_cost',
                'reference_type',
                'reference_id',
                'transaction_date',
                'performed_by',
                'remarks',
            ],
        ])
        ->assertJsonPath('data.id', $transaction->id)
        ->assertJsonPath(
            'data.transaction_number',
            $transaction->transaction_number
        )
        ->assertJsonPath(
            'data.transaction_type',
            'receipt'
        )
        ->assertJsonPath(
            'data.quantity',
            '10.0000'
        )
        ->assertJsonPath(
            'data.unit_cost',
            '1500.0000'
        )
        ->assertJsonPath(
            'data.remarks',
            'Initial receipt'
        );
});

it('returns 404 for a missing inventory transaction', function () {
    $this->getJson(
        route('inventory-transactions.show', 999999)
    )
        ->assertNotFound();
});