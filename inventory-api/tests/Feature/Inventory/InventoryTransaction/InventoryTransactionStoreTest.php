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
    $this->admin = $this->actingAsAdmin();

    DB::statement(
        'ALTER SEQUENCE inventory_transaction_number_sequence RESTART WITH 1'
    );
});

it('creates a receipt transaction', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'unit_cost' => 1500,
            'transaction_date' => now()->toDateTimeString(),
            'remarks' => 'Initial receipt',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.transaction_type', 'receipt')
        ->assertJsonPath('data.item_id', $item->id)
        ->assertJsonPath('data.warehouse_id', $warehouse->id)
        ->assertJsonPath('data.quantity', '10.0000')
        ->assertJsonPath('data.unit_cost', '1500.0000')
        ->assertJsonPath('data.remarks', 'Initial receipt');

    expect(InventoryTransaction::query()->count())
        ->toBe(1);
});

it('generates the transaction number automatically', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.transaction_number',
            'TRX-000001'
        );
});

it('does not allow the client to choose the transaction number', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_number' => 'HACK-999999',
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.transaction_number',
            'TRX-000001'
        );
});

it('generates sequential transaction numbers', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $payload = [
        'transaction_type' => 'receipt',
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 10,
    ];

    $first = $this->postJson(
        route('inventory-transactions.store'),
        $payload
    );

    $second = $this->postJson(
        route('inventory-transactions.store'),
        $payload
    );

    $first
        ->assertCreated()
        ->assertJsonPath(
            'data.transaction_number',
            'TRX-000001'
        );

    $second
        ->assertCreated()
        ->assertJsonPath(
            'data.transaction_number',
            'TRX-000002'
        );
});

it('records the authenticated user as the performer', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.performed_by',
            $this->admin->id
        );
});

it('requires a transaction type', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('transaction_type');
});

it('rejects an invalid transaction type', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'invalid',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('transaction_type');
});

it('requires an item', function () {
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('requires an existing item', function () {
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => 999999,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('requires a warehouse', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('warehouse_id');
});

it('requires an existing warehouse', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => 999999,
            'quantity' => 10,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('warehouse_id');
});

it('requires a positive quantity', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 0,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('quantity');
});

it('rejects a negative quantity', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => -5,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('quantity');
});

it('accepts an optional unit cost', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('inventory-transactions.store'),
        [
            'transaction_type' => 'receipt',
            'item_id' => $item->id,
            'warehouse_id' => $warehouse->id,
            'quantity' => 10,
            'unit_cost' => 1250.50,
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.unit_cost', '1250.5000');
});