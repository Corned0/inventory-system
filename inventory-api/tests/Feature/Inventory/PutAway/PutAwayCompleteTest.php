<?php

use App\Enums\InventoryTransactionType;
use App\Enums\PutAwayStatus;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Location;
use App\Models\PutAway;
use App\Models\PutAwayItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Services\Inventory\InventoryTransactionNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

it('completes a put-away', function () {
    $this->actingAsAdmin();

    $item = Item::factory()->create();

    $supplier = Supplier::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $receiving = Receiving::factory()->create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $receivingItem = ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'accepted_quantity' => 10,
        'unit_cost' => 1500,
    ]);

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $putAway = PutAway::factory()->create([
        'receiving_id' => $receiving->id,
        'warehouse_id' => $warehouse->id,
        'status' => PutAwayStatus::Draft,
    ]);

    PutAwayItem::factory()->create([
        'put_away_id' => $putAway->id,
        'receiving_item_id' => $receivingItem->id,
        'item_id' => $item->id,
        'location_id' => $location->id,
        'quantity' => 10,
    ]);

    $response = $this->postJson(
        route(
            'put-aways.complete',
            $putAway
        )
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.status',
            PutAwayStatus::Completed->value
        );

    expect(
        InventoryTransaction::query()
            ->where(
                'transaction_type',
                InventoryTransactionType::PutAway->value
            )
            ->count()
    )->toBe(1);

    $transaction = InventoryTransaction::query()
        ->first();

    expect($transaction)
        ->not->toBeNull();

    expect($transaction->item_id)
        ->toBe($item->id);

    expect($transaction->warehouse_id)
        ->toBe($warehouse->id);

    expect($transaction->location_id)
        ->toBe($location->id);

    expect((float) $transaction->quantity)
        ->toBe(10.0);
});

it('cannot complete a completed put-away', function () {
    $this->actingAsAdmin();

    $putAway = PutAway::factory()->create([
        'status' => PutAwayStatus::Completed,
    ]);

    $this->postJson(
        route(
            'put-aways.complete',
            $putAway
        )
    )
        ->assertStatus(409);
});

it('does not create transactions when completion fails', function () {
    $this->actingAsAdmin();

    $item = Item::factory()->create();

    $supplier = Supplier::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $receiving = Receiving::factory()->create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
    ]);

    $receivingItem = ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'accepted_quantity' => 10,
    ]);

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $putAway = PutAway::factory()->create([
        'receiving_id' => $receiving->id,
        'warehouse_id' => $warehouse->id,
        'status' => PutAwayStatus::Draft,
    ]);

    PutAwayItem::factory()->create([
        'put_away_id' => $putAway->id,
        'receiving_item_id' => $receivingItem->id,
        'item_id' => $item->id,
        'location_id' => $location->id,
        'quantity' => 10,
    ]);

    $this->mock(
        InventoryTransactionNumberGenerator::class,
        function ($mock): void {
            $mock->shouldReceive('generate')
                ->once()
                ->andThrow(new RuntimeException('Unable to generate transaction number.'));
        }
    );

    $this->postJson(
        route(
            'put-aways.complete',
            $putAway
        )
    )
        ->assertStatus(500);

    expect(
        InventoryTransaction::query()
            ->where('reference_type', PutAway::class)
            ->where('reference_id', $putAway->id)
            ->count()
    )->toBe(0);

    expect(
        $putAway->refresh()->status
    )->toBe(PutAwayStatus::Draft);
});