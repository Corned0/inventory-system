<?php

use App\Enums\InventoryTransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryBalanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

function createInventoryTransaction(
    InventoryTransactionType $type,
    Item $item,
    Warehouse $warehouse,
    float|int|string $quantity,
    ?int $locationId = null,
    ?int $lotId = null,
): InventoryTransaction {
    return InventoryTransaction::factory()->create([
        'transaction_type' => $type,
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'location_id' => $locationId,
        'lot_id' => $lotId,
        'quantity' => $quantity,
    ]);
}

it('increases stock for a receipt', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $transaction = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $balance = app(InventoryBalanceService::class)
        ->apply($transaction);

    expect($balance->quantity)
        ->toBe('100.0000');

    expect($balance->reserved_quantity)
        ->toBe('0.0000');

    expect($balance->available_quantity)
        ->toBe('100.0000');
});

it('accumulates multiple receipts', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $first = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $second = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        50
    );

    $service->apply($first);
    $balance = $service->apply($second);

    expect($balance->quantity)
        ->toBe('150.0000');

    expect($balance->available_quantity)
        ->toBe('150.0000');
});

it('decreases stock for an issue', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $issue = createInventoryTransaction(
        InventoryTransactionType::Issue,
        $item,
        $warehouse,
        30
    );

    $service->apply($receipt);

    $balance = $service->apply($issue);

    expect($balance->quantity)
        ->toBe('70.0000');

    expect($balance->available_quantity)
        ->toBe('70.0000');
});

it('does not allow issuing more than available stock', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        50
    );

    $issue = createInventoryTransaction(
        InventoryTransactionType::Issue,
        $item,
        $warehouse,
        60
    );

    $service->apply($receipt);

    expect(
        fn () => $service->apply($issue)
    )->toThrow(InsufficientStockException::class);

    $balance = InventoryBalance::query()->first();

    expect($balance->quantity)
        ->toBe('50.0000');
});

it('decreases stock for transfer out', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $transferOut = createInventoryTransaction(
        InventoryTransactionType::TransferOut,
        $item,
        $warehouse,
        25
    );

    $service->apply($receipt);

    $balance = $service->apply($transferOut);

    expect($balance->quantity)
        ->toBe('75.0000');
});

it('increases stock for transfer in', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $transaction = createInventoryTransaction(
        InventoryTransactionType::TransferIn,
        $item,
        $warehouse,
        25
    );

    $balance = $service->apply($transaction);

    expect($balance->quantity)
        ->toBe('25.0000');
});

it('increases stock for a return', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $transaction = createInventoryTransaction(
        InventoryTransactionType::Return,
        $item,
        $warehouse,
        15
    );

    $balance = $service->apply($transaction);

    expect($balance->quantity)
        ->toBe('15.0000');
});

it('increases stock for adjustment in', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $adjustment = createInventoryTransaction(
        InventoryTransactionType::AdjustmentIn,
        $item,
        $warehouse,
        5
    );

    $service->apply($receipt);

    $balance = $service->apply($adjustment);

    expect($balance->quantity)
        ->toBe('105.0000');
});

it('decreases stock for adjustment out', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $adjustment = createInventoryTransaction(
        InventoryTransactionType::AdjustmentOut,
        $item,
        $warehouse,
        10
    );

    $service->apply($receipt);

    $balance = $service->apply($adjustment);

    expect($balance->quantity)
        ->toBe('90.0000');
});

it('decreases stock for disposal', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $disposal = createInventoryTransaction(
        InventoryTransactionType::Disposal,
        $item,
        $warehouse,
        20
    );

    $service->apply($receipt);

    $balance = $service->apply($disposal);

    expect($balance->quantity)
        ->toBe('80.0000');
});

it('maintains separate balances by location', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $locationA = \App\Models\Location::factory()
        ->for($warehouse)
        ->create();

    $locationB = \App\Models\Location::factory()
        ->for($warehouse)
        ->create();

    $service = app(InventoryBalanceService::class);

    $transactionA = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100,
        $locationA->id
    );

    $transactionB = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        50,
        $locationB->id
    );

    $service->apply($transactionA);
    $service->apply($transactionB);

    expect(
        InventoryBalance::query()
            ->where('item_id', $item->id)
            ->count()
    )->toBe(2);

    expect(
        InventoryBalance::query()
            ->where('location_id', $locationA->id)
            ->value('quantity')
    )->toBe('100.0000');

    expect(
        InventoryBalance::query()
            ->where('location_id', $locationB->id)
            ->value('quantity')
    )->toBe('50.0000');
});

it('maintains separate balances by lot', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $lotA = \App\Models\InventoryLot::factory()
        ->create([
            'item_id' => $item->id,
        ]);

    $lotB = \App\Models\InventoryLot::factory()
        ->create([
            'item_id' => $item->id,
        ]);

    $service = app(InventoryBalanceService::class);

    $transactionA = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100,
        null,
        $lotA->id
    );

    $transactionB = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        50,
        null,
        $lotB->id
    );

    $service->apply($transactionA);
    $service->apply($transactionB);

    expect(
        InventoryBalance::query()
            ->where('lot_id', $lotA->id)
            ->value('quantity')
    )->toBe('100.0000');

    expect(
        InventoryBalance::query()
            ->where('lot_id', $lotB->id)
            ->value('quantity')
    )->toBe('50.0000');
});

it('calculates available quantity from reserved quantity', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $balance = InventoryBalance::factory()->create([
        'item_id' => $item->id,
        'warehouse_id' => $warehouse->id,
        'quantity' => 100,
        'reserved_quantity' => 25,
        'available_quantity' => 75,
    ]);

    expect($balance->available_quantity)
        ->toBe('75.0000');
});

it('sets the balance to the physical stock count', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $service = app(InventoryBalanceService::class);

    $receipt = createInventoryTransaction(
        InventoryTransactionType::Receipt,
        $item,
        $warehouse,
        100
    );

    $stockCount = createInventoryTransaction(
        InventoryTransactionType::StockCount,
        $item,
        $warehouse,
        93
    );

    $service->apply($receipt);

    $balance = $service->apply($stockCount);

    expect($balance->quantity)
        ->toBe('93.0000');

    expect($balance->available_quantity)
        ->toBe('93.0000');
});