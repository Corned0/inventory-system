<?php

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

it('deletes a BOM component', function () {
    $bom = ItemBom::factory()->create();

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bom->id,
    ]);

    $this->deleteJson(
        route(
            'item-bom-components.destroy',
            [
                'bom' => $bom,
                'component' => $component,
            ]
        )
    )
        ->assertOk()
        ->assertJsonPath(
            'message',
            'BOM component deleted successfully.'
        );

    expect(
        ItemBomComponent::query()
            ->whereKey($component->id)
            ->exists()
    )->toBeFalse();
});

it('does not delete a component belonging to another BOM', function () {
    $bomA = ItemBom::factory()->create();
    $bomB = ItemBom::factory()->create();

    $component = ItemBomComponent::factory()->create([
        'bom_id' => $bomB->id,
    ]);

    $this->deleteJson(
        route(
            'item-bom-components.destroy',
            [
                'bom' => $bomA,
                'component' => $component,
            ]
        )
    )
        ->assertNotFound();

    expect(
        ItemBomComponent::query()
            ->whereKey($component->id)
            ->exists()
    )->toBeTrue();
});