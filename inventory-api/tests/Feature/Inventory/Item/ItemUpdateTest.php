<?php

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

it('updates an item', function () {
    $item = Item::factory()->create();

    $this->patchJson(
        route('items.update', $item),
        [
            'name' => 'Updated Computer',
        ]
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item updated successfully.',
        ]);

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'name' => 'Updated Computer',
    ]);
});

it('allows keeping the existing barcode', function () {
    $item = Item::factory()->create([
        'barcode' => '123456789',
    ]);

    $this->patchJson(
        route('items.update', $item),
        [
            'barcode' => '123456789',
        ]
    )
        ->assertOk();
});

it('rejects a duplicate barcode', function () {
    Item::factory()->create([
        'barcode' => '123456789',
    ]);

    $item = Item::factory()->create();

    $this->patchJson(
        route('items.update', $item),
        [
            'barcode' => '123456789',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('barcode');
});