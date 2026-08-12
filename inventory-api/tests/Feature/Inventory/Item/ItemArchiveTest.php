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

it('archives an item', function () {
    $item = Item::factory()->create();

    $this->deleteJson(
        route('items.destroy', $item)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item archived successfully.',
        ]);

    $this->assertSoftDeleted('items', [
        'id' => $item->id,
    ]);
});

it('restores an archived item', function () {
    $item = Item::factory()->create();
    $item->delete();

    $this->postJson(
        route('items.restore', $item)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item restored successfully.',
        ]);

    $this->assertDatabaseHas('items', [
        'id' => $item->id,
        'deleted_at' => null,
    ]);
});