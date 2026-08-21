<?php

use App\Enums\PutAwayStatus;
use App\Enums\ReceivingStatus;
use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;
use Illuminate\Support\Facades\DB;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->admin = $this->actingAsAdmin();

    DB::statement(
        'ALTER SEQUENCE put_away_number_sequence RESTART WITH 1'
    );
});

function createAcceptedReceiving(): array
{
    $item = Item::factory()->create();

    $supplier = Supplier::factory()->create();

    $warehouse = Warehouse::factory()->create();

    $receiving = Receiving::factory()->create([
        'supplier_id' => $supplier->id,
        'warehouse_id' => $warehouse->id,
        'status' => ReceivingStatus::Accepted,
    ]);

    $receivingItem = ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
        'ordered_quantity' => 10,
        'received_quantity' => 10,
        'accepted_quantity' => 10,
        'rejected_quantity' => 0,
        'unit_cost' => 1500,
    ]);

    $location = \App\Models\Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    return [
        $receiving,
        $receivingItem,
        $location,
        $item,
        $warehouse,
    ];
}

it('creates a draft put-away', function () {
    [
        $receiving,
        $receivingItem,
        $location,
        $item,
        $warehouse,
    ] = createAcceptedReceiving();

    $response = $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 10,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.status',
            PutAwayStatus::Draft->value
        )
        ->assertJsonPath(
            'data.receiving_id',
            $receiving->id
        )
        ->assertJsonPath(
            'data.warehouse_id',
            $warehouse->id
        )
        ->assertJsonPath(
            'data.items.0.receiving_item_id',
            $receivingItem->id
        )
        ->assertJsonPath(
            'data.items.0.item_id',
            $item->id
        )
        ->assertJsonPath(
            'data.items.0.location_id',
            $location->id
        )
        ->assertJsonPath(
            'data.items.0.quantity',
            '10.0000'
        );
});

it('generates the put-away number automatically', function () {
    [
        $receiving,
        $receivingItem,
        $location,
    ] = createAcceptedReceiving();

    $response = $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 10,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.put_away_number',
            'PA-000001'
        );
});

it('requires at least one put-away item', function () {
    [
        $receiving,
    ] = createAcceptedReceiving();

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');
});

it('requires receiving item', function () {
    [
        $receiving,
        $receivingItem,
        $location,
    ] = createAcceptedReceiving();

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'location_id' => $location->id,
                    'quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'items.0.receiving_item_id'
        );
});

it('requires location', function () {
    [
        $receiving,
        $receivingItem,
    ] = createAcceptedReceiving();

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'items.0.location_id'
        );
});

it('requires positive quantity', function () {
    [
        $receiving,
        $receivingItem,
        $location,
    ] = createAcceptedReceiving();

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 0,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'items.0.quantity'
        );
});

it('does not allow put-away from draft receiving', function () {
    [
        $receiving,
        $receivingItem,
        $location,
    ] = createAcceptedReceiving();

    $receiving->update([
        'status' => ReceivingStatus::Draft,
    ]);

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 10,
                ],
            ],
        ]
    )
        ->assertStatus(409);
});

it('does not allow quantity greater than accepted quantity', function () {
    [
        $receiving,
        $receivingItem,
        $location,
    ] = createAcceptedReceiving();

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 11,
                ],
            ],
        ]
    )
        ->assertStatus(409);
});

it('does not allow a location from another warehouse', function () {
    [
        $receiving,
        $receivingItem,
    ] = createAcceptedReceiving();

    $otherWarehouse = Warehouse::factory()->create();

    $location = \App\Models\Location::factory()->create([
        'warehouse_id' => $otherWarehouse->id,
    ]);

    $this->postJson(
        route(
            'receivings.put-away',
            $receiving
        ),
        [
            'items' => [
                [
                    'receiving_item_id' => $receivingItem->id,
                    'location_id' => $location->id,
                    'quantity' => 10,
                ],
            ],
        ]
    )
        ->assertStatus(409);
});