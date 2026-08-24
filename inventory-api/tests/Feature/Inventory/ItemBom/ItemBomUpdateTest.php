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

it('shows a BOM', function () {
    $item = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $item->id,
        'name' => 'Dell Precision BOM',
        'version' => 1,
    ]);

    $this->getJson(
        route('item-boms.show', $bom)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $bom->id)
        ->assertJsonPath('data.item.id', $item->id)
        ->assertJsonPath(
            'data.name',
            'Dell Precision BOM'
        )
        ->assertJsonPath('data.version', 1);
});

it('includes components when showing a BOM', function () {
    $item = Item::factory()->create();

    $component = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $item->id,
    ]);

    ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'component_item_id' => $component->id,
        'quantity' => 2,
    ]);

    $this->getJson(
        route('item-boms.show', $bom)
    )
        ->assertOk()
        ->assertJsonPath(
            'data.components.0.component_item.id',
            $component->id
        );
});