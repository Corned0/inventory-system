<?php

use App\Enums\AssetInstanceStatus;
use App\Models\AssetInstance;
use App\Models\Item;
use App\Models\Location;
use App\Models\Warehouse;
use App\Services\Inventory\AssetNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;
use Illuminate\Support\Facades\DB;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();

    DB::statement(
        'ALTER SEQUENCE asset_number_sequence RESTART WITH 1'
    );
});

it('creates an asset instance', function () {
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000001'
        )
        ->assertJsonPath(
            'data.serial_number',
            'ABC123456'
        )
        ->assertJsonPath(
            'data.status',
            AssetInstanceStatus::Available->value
        )
        ->assertJsonPath(
            'data.item.id',
            $item->id
        );

    expect(AssetInstance::query()->count())
        ->toBe(1);
});

it('generates asset number automatically', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000001'
        );
});

it('does not allow the client to choose the asset number', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'asset_number' => 'HACK-999999',
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000001'
        );
});

it('generates sequential asset numbers', function () {
    $item = Item::factory()->create();

    $first = $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    );

    $second = $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'DEF123456',
        ]
    );

    $first
        ->assertCreated()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000001'
        );

    $second
        ->assertCreated()
        ->assertJsonPath(
            'data.asset_number',
            'AST-000002'
        );
});

it('requires an item', function () {
    $this->postJson(
        route('asset-instances.store'),
        [
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('rejects a nonexistent item', function () {
    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => 999999,
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('item_id');
});

it('requires a serial number', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

it('rejects duplicate serial number', function () {
    $item = Item::factory()->create();

    AssetInstance::factory()->create([
        'item_id' => $item->id,
        'serial_number' => 'ABC123456',
    ]);

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('serial_number');
});

it('defaults status to available', function () {
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath(
            'data.status',
            AssetInstanceStatus::Available->value
        );

    expect(
        AssetInstance::query()->first()->status
    )->toBe(AssetInstanceStatus::Available);
});

it('accepts a valid status', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'status' => AssetInstanceStatus::Repair->value,
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.status',
            AssetInstanceStatus::Repair->value
        );
});

it('rejects an invalid status', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'status' => 'invalid',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('status');
});

it('accepts a warehouse', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'current_warehouse_id' => $warehouse->id,
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.current_warehouse.id',
            $warehouse->id
        );
});

it('rejects a nonexistent warehouse', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'current_warehouse_id' => 999999,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'current_warehouse_id'
        );
});

it('accepts a location', function () {
    $item = Item::factory()->create();
    $warehouse = Warehouse::factory()->create();

    $location = Location::factory()->create([
        'warehouse_id' => $warehouse->id,
    ]);

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'current_warehouse_id' => $warehouse->id,
            'current_location_id' => $location->id,
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.current_location.id',
            $location->id
        );
});

it('rejects a nonexistent location', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'current_location_id' => 999999,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'current_location_id'
        );
});

it('accepts acquisition cost', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'acquisition_cost' => 125000.50,
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.acquisition_cost',
            '125000.5000'
        );
});

it('rejects a negative acquisition cost', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'acquisition_cost' => -100,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'acquisition_cost'
        );
});

it('accepts warranty dates', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'warranty_start' => '2026-01-01',
            'warranty_end' => '2029-01-01',
        ]
    )
        ->assertCreated()
        ->assertJsonPath(
            'data.warranty_start',
            '2026-01-01'
        )
        ->assertJsonPath(
            'data.warranty_end',
            '2029-01-01'
        );
});

it('rejects warranty end before warranty start', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('asset-instances.store'),
        [
            'item_id' => $item->id,
            'serial_number' => 'ABC123456',
            'warranty_start' => '2029-01-01',
            'warranty_end' => '2026-01-01',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors(
            'warranty_end'
        );
});