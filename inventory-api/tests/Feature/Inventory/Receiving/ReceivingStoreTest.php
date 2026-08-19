<?php

use App\Models\Receiving;
use App\Models\Supplier;
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
        'ALTER SEQUENCE receiving_number_sequence RESTART WITH 1'
    );
});

it('creates a draft receiving', function () {
    $item = Item::factory()->create();
    $supplier = Supplier::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $response = $this->postJson(
        route('receivings.store'),
        [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'remarks' => 'Initial delivery',

            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                    'received_quantity' => 9,
                    'unit_cost' => 1500,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.status', 'draft')
        ->assertJsonPath('data.supplier_id', $supplier->id)
        ->assertJsonPath('data.warehouse_id', $warehouse->id)
        ->assertJsonPath('data.remarks', 'Initial delivery')
        ->assertJsonPath('data.items.0.item_id', $item->id)
        ->assertJsonPath('data.items.0.ordered_quantity', '10.0000')
        ->assertJsonPath('data.items.0.received_quantity', '9.0000')
        ->assertJsonPath('data.items.0.unit_cost', '1500.0000');
});

it('generates the receiving number automatically', function () {
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('receivings.store'),
        [
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                    'received_quantity' => 10,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.receiving_number',
            'RCV-000001'
        );
});

it('does not allow the client to choose the receiving number', function () {
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('receivings.store'),
        [
            'receiving_number' => 'HACK-999999',
            'supplier_id' => $supplier->id,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                    'received_quantity' => 10,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.receiving_number',
            'RCV-000001'
        );
});

it('records the authenticated user as received by', function () {
    $warehouse = Warehouse::factory()->create();
    $supplier = Supplier::factory()->create();
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'supplier_id' => $supplier->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                    'received_quantity' => 10,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.received_by',
            $this->admin->id
        );
});

it('requires a warehouse', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('warehouse_id');
});

it('requires an existing warehouse', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => 999999,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('warehouse_id');
});

it('rejects a nonexistent supplier', function () {
    $warehouse = Warehouse::factory()->create();
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'supplier_id' => 999999,
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('supplier_id');
});

it('requires receiving items', function () {
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items');
});

it('requires an item for each receiving item', function () {
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.item_id');
});

it('rejects a nonexistent item', function () {
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => 999999,
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.item_id');
});

it('requires a positive ordered quantity', function () {
    $warehouse = Warehouse::factory()->create();
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 0,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.ordered_quantity');
});

it('rejects a negative ordered quantity', function () {
    $warehouse = Warehouse::factory()->create();
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => -10,
                ],
            ],
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('items.0.ordered_quantity');
});

/* it('accepts an optional unit cost', function () {
    $warehouse = Warehouse::factory()->create();
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                    'unit_cost' => 1250.50,
                ],
            ],
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.items.0.unit_cost',
            '1250.5000'
        );
});

it('accepts an optional supplier', function () {
    $warehouse = Warehouse::factory()->create();
    $item = Item::factory()->create();

    $this->postJson(
        route('receivings.store'),
        [
            'warehouse_id' => $warehouse->id,
            'items' => [
                [
                    'item_id' => $item->id,
                    'ordered_quantity' => 10,
                ],
            ],
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.supplier_id', null);
}); */