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

it('returns an item', function () {
    $item = Item::factory()->create([
        'name' => 'Dell Precision 3680',
    ]);

    $this->getJson(
        route('items.show', $item)
    )
        ->assertOk()
        ->assertJsonPath('data.id', $item->id)
        ->assertJsonPath('data.name', 'Dell Precision 3680')
        ->assertJsonPath('data.item_code', $item->item_code);
});

it('returns 404 for a missing item', function () {
    $this->getJson(
        route('items.show', 999999)
    )
        ->assertNotFound();
});