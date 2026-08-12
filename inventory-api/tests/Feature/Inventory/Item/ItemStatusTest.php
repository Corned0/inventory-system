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

it('activates an item', function () {
    $item = Item::factory()->inactive()->create();

    $this->postJson(
        route('items.activate', $item)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item activated successfully.',
        ])
        ->assertJsonPath('data.is_active', true);
});

it('deactivates an item', function () {
    $item = Item::factory()->create([
        'is_active' => true,
    ]);

    $this->postJson(
        route('items.deactivate', $item)
    )
        ->assertOk()
        ->assertJson([
            'message' => 'Item deactivated successfully.',
        ])
        ->assertJsonPath('data.is_active', false);
});