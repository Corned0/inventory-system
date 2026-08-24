<?php

use App\Models\Item;
use App\Models\ItemBom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('updates a BOM name', function () {
    $bom = ItemBom::factory()->create([
        'name' => 'Old BOM',
    ]);

    $this->patchJson(
        route('item-boms.update', $bom),
        [
            'name' => 'Updated BOM',
        ]
    )
        ->assertOk()
        ->assertJsonPath(
            'data.name',
            'Updated BOM'
        );
});

it('updates the BOM version', function () {
    $bom = ItemBom::factory()->create([
        'version' => 1,
    ]);

    $this->patchJson(
        route('item-boms.update', $bom),
        [
            'version' => 2,
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.version', 2);
});

it('deactivates a BOM', function () {
    $bom = ItemBom::factory()->create([
        'is_active' => true,
    ]);

    $this->patchJson(
        route('item-boms.update', $bom),
        [
            'is_active' => false,
        ]
    )
        ->assertOk()
        ->assertJsonPath('data.is_active', false);
});

it('does not allow activating a BOM when another active BOM exists', function () {
    $item = Item::factory()->create();

    $activeBom = ItemBom::factory()->create([
        'item_id' => $item->id,
        'version' => 1,
        'is_active' => true,
    ]);

    $inactiveBom = ItemBom::factory()->create([
        'item_id' => $item->id,
        'version' => 2,
        'is_active' => false,
    ]);

    $this->patchJson(
        route('item-boms.update', $inactiveBom),
        [
            'is_active' => true,
        ]
    )
        ->assertStatus(409);

    expect($activeBom->refresh()->is_active)->toBeTrue();
    expect($inactiveBom->refresh()->is_active)->toBeFalse();
});