<?php

use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Models\Item;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('shows a receiving', function () {
    $receiving = Receiving::factory()->create();

    $response = $this->getJson(
        route('receivings.show', $receiving)
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.id',
            $receiving->id
        )
        ->assertJsonPath(
            'data.receiving_number',
            $receiving->receiving_number
        );
});

it('includes receiving items', function () {
    $receiving = Receiving::factory()->create();
    $item = Item::factory()->create();

    ReceivingItem::factory()->create([
        'receiving_id' => $receiving->id,
        'item_id' => $item->id,
    ]);

    $response = $this->getJson(
        route('receivings.show', $receiving)
    );

    $response
        ->assertOk()
        ->assertJsonPath(
            'data.items.0.item_id',
            $item->id
        );
});

it('returns not found for nonexistent receiving', function () {
    $this->getJson(
        route('receivings.show', 999999)
    )
        ->assertNotFound();
});