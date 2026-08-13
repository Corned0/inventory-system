<?php

use App\Models\InventoryTransaction;
use App\Models\Item;
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

it('returns paginated inventory transactions', function () {
    InventoryTransaction::factory()
        ->count(3)
        ->create();

    $this->getJson(
        route('inventory-transactions.index')
    )
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'transaction_number',
                    'transaction_type',
                    'item_id',
                    'warehouse_id',
                    'quantity',
                    'unit_cost',
                    'transaction_date',
                    'performed_by',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ])
        ->assertJsonPath('meta.total', 3);
});

it('filters transactions by item', function () {
    $item = Item::factory()->create();
    $otherItem = Item::factory()->create();

    $transaction = InventoryTransaction::factory()->create([
        'item_id' => $item->id,
    ]);

    InventoryTransaction::factory()->create([
        'item_id' => $otherItem->id,
    ]);

    $this->getJson(
        route('inventory-transactions.index', [
            'item_id' => $item->id,
        ])
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $transaction->id);
});

it('filters transactions by warehouse', function () {
    $warehouse = Warehouse::factory()->create();
    $otherWarehouse = Warehouse::factory()->create();

    $transaction = InventoryTransaction::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    InventoryTransaction::factory()->create([
        'warehouse_id' => $otherWarehouse->id,
    ]);

    $this->getJson(
        route('inventory-transactions.index', [
            'warehouse_id' => $warehouse->id,
        ])
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $transaction->id);
});

it('filters transactions by transaction type', function () {
    InventoryTransaction::factory()->create([
        'transaction_type' => 'receipt',
    ]);

    $issue = InventoryTransaction::factory()->create([
        'transaction_type' => 'issue',
    ]);

    $this->getJson(
        route('inventory-transactions.index', [
            'transaction_type' => 'issue',
        ])
    )
        ->assertOk()
        ->assertJsonCount(1, 'data')
        ->assertJsonPath('data.0.id', $issue->id);
});