<?php

use App\Models\ItemType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns paginated item types', function () {
    ItemType::factory()->count(25)->create();

    $response = $this->getJson(route('item-types.index'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'description',
                    'tracking_type',
                    'is_asset',
                    'is_composite',
                    'is_active',
                    'created_at',
                    'updated_at',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);

    expect($response->json('meta.total'))->toBe(25);
});

it('returns item type data', function () {
    $itemType = ItemType::factory()->create([
        'code' => 'COMPUTER',
        'name' => 'Computer Equipment',
        'tracking_type' => 'serial',
        'is_asset' => true,
        'is_composite' => true,
        'is_active' => true,
    ]);

    $this->getJson(route('item-types.index'))
        ->assertOk()
        ->assertJsonFragment([
            'id' => $itemType->id,
            'code' => 'COMPUTER',
            'name' => 'Computer Equipment',
            'tracking_type' => 'serial',
            'is_asset' => true,
            'is_composite' => true,
            'is_active' => true,
        ]);
});

it('rejects guest requests', function () {
    auth()->forgetGuards();

    $this->getJson(route('item-types.index'))
        ->assertUnauthorized();
});