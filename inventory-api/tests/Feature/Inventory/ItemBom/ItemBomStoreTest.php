<?php

use App\Exceptions\ActiveBomExistsException;
use App\Models\Item;
use App\Models\ItemBom;
use App\Models\Supplier;
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

it('creates a BOM', function () {
    $item = Item::factory()->create();

    $response = $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v1',
            'version' => 1,
        ]
    );

    $response
        ->assertCreated()
        ->assertJsonPath('data.item.id', $item->id)
        ->assertJsonPath('data.name', 'BOM v1')
        ->assertJsonPath('data.version', 1)
        ->assertJsonPath('data.is_active', true);

    expect(ItemBom::query()->count())->toBe(1);
});

it('defaults BOM to active', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v1',
            'version' => 1,
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.is_active', true);
});

it('allows creating an inactive BOM', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v1',
            'version' => 1,
            'is_active' => false,
        ]
    )
        ->assertCreated()
        ->assertJsonPath('data.is_active', false);
});

it('requires a BOM name', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $item),
        [
            'version' => 1,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires a BOM version', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v1',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('version');
});

it('requires a positive BOM version', function () {
    $item = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v1',
            'version' => 0,
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('version');
});

it('does not allow duplicate BOM versions for an item', function () {
    $item = Item::factory()->create();

    ItemBom::factory()->create([
        'item_id' => $item->id,
        'version' => 1,
        'is_active' => false,
    ]);

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'Another BOM',
            'version' => 1,
            'is_active' => false,
        ]
    )
        ->assertStatus(500);
});

it('allows the same BOM version for different items', function () {
    $itemA = Item::factory()->create();
    $itemB = Item::factory()->create();

    $this->postJson(
        route('item-boms.store', $itemA),
        [
            'name' => 'BOM v1',
            'version' => 1,
        ]
    )->assertCreated();

    $this->postJson(
        route('item-boms.store', $itemB),
        [
            'name' => 'BOM v1',
            'version' => 1,
        ]
    )->assertCreated();

    expect(ItemBom::query()->count())->toBe(2);
});

it('does not allow multiple active BOMs for the same item', function () {
    $item = Item::factory()->create();

    ItemBom::factory()->create([
        'item_id' => $item->id,
        'version' => 1,
        'is_active' => true,
    ]);

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v2',
            'version' => 2,
            'is_active' => true,
        ]
    )
        ->assertStatus(409);
});

it('allows multiple inactive BOMs for the same item', function () {
    $item = Item::factory()->create();

    ItemBom::factory()->create([
        'item_id' => $item->id,
        'version' => 1,
        'is_active' => false,
    ]);

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'BOM v2',
            'version' => 2,
            'is_active' => false,
        ]
    )
        ->assertCreated();
});