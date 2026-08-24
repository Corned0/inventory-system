<?php

use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemBomComponent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns BOMs for an item', function () {
    $item = Item::factory()->create();

    ItemBom::factory()->create([
        'item_id' => $item->id,
        'name' => 'BOM v1',
        'version' => 1,
    ]);

    ItemBom::factory()->create([
        'item_id' => $item->id,
        'name' => 'BOM v2',
        'version' => 2,
        'is_active' => false,
    ]);

    $response = $this->getJson(
        route('item-boms.index', $item)
    );

    $response
        ->assertOk()
        ->assertJsonCount(2, 'data')
        ->assertJsonPath('data.0.version', 2)
        ->assertJsonPath('data.1.version', 1);
});

it('does not return BOMs belonging to another item', function () {
    $item = Item::factory()->create();
    $otherItem = Item::factory()->create();

    ItemBom::factory()->create([
        'item_id' => $otherItem->id,
    ]);

    $this->getJson(
        route('item-boms.index', $item)
    )
        ->assertOk()
        ->assertJsonCount(0, 'data');
});

it('includes BOM components', function () {
    $item = Item::factory()->create();

    $componentItem = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $item->id,
    ]);

    ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'component_item_id' => $componentItem->id,
        'quantity' => 2,
    ]);

    $this->getJson(
        route('item-boms.index', $item)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.0.components.0.component_item.id',
            $componentItem->id
        )
        ->assertJsonPath(
            'data.0.components.0.quantity',
            '2.0000'
        );
});