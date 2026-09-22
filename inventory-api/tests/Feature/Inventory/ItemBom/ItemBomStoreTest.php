<?php

use App\Exceptions\ActiveBomExistsException;
use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemType;
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

function makeCompositeItem(): Item
{
    return Item::factory()->create([
        'item_type_id' => ItemType::factory()->create([
            'is_composite' => true,
        ])->id,
    ]);
}

it('creates a BOM', function () {
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $itemA = makeCompositeItem();
    $itemB = makeCompositeItem();

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
    $item = makeCompositeItem();

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
    $item = makeCompositeItem();

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

it('rejects creating a BOM for a non-composite item', function () {
    $item = Item::factory()->create([
        'item_type_id' => ItemType::factory()->create([
            'is_composite' => false,
        ])->id,
    ]);

    $this->postJson(
        route('item-boms.store', $item),
        [
            'name' => 'Not allowed',
            'version' => 1,
        ]
    )
        ->assertStatus(500);
});