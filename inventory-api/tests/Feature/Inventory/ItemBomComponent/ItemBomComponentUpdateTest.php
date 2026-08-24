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

it('updates component quantity', function () {
    $item = Item::factory()->create();
    $componentItem = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $item->id,
    ]);

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'component_item_id' => $componentItem->id,
        'quantity' => 1,
    ]);

    $this->patchJson(
        route(
            'item-bom-components.update',
            [
                'bom' => $bom,
                'component' => $component,
            ]
        ),
        [
            'quantity' => 4,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.quantity',
            '4.0000'
        );
});

it('updates component required flag', function () {
    $bom = ItemBom::factory()->create();

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'is_required' => true,
    ]);

    $this->patchJson(
        route(
            'item-bom-components.update',
            [
                'bom' => $bom,
                'component' => $component,
            ]
        ),
        [
            'is_required' => false,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.is_required',
            false
        );
});

it('updates the component item', function () {
    $bomItem = Item::factory()->create();
    $oldComponent = Item::factory()->create();
    $newComponent = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $bomItem->id,
    ]);

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'component_item_id' => $oldComponent->id,
    ]);

    $this->patchJson(
        route(
            'item-bom-components.update',
            [
                'bom' => $bom,
                'component' => $component,
            ]
        ),
        [
            'component_item_id' => $newComponent->id,
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.component_item.id',
            $newComponent->id
        );
});

it('rejects changing component to the BOM item itself', function () {
    $item = Item::factory()->create();
    $componentItem = Item::factory()->create();

    $bom = ItemBom::factory()->create([
        'item_id' => $item->id,
    ]);

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
        'component_item_id' => $componentItem->id,
    ]);

    $this->patchJson(
        route(
            'item-bom-components.update',
            [
                'bom' => $bom,
                'component' => $component,
            ]
        ),
        [
            'component_item_id' => $item->id,
        ]
    )
        ->assertStatus(422);
});