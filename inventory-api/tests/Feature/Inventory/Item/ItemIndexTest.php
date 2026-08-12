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

it('returns paginated items', function () {
    Item::factory()->count(3)->create();

    $this->getJson(
        route('items.index')
    )
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'item_code',
                    'barcode',
                    'name',
                    'description',
                    'category',
                    'item_type',
                    'unit',
                    'reorder_level',
                    'reorder_quantity',
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
        ])
        ->assertJsonPath('meta.total', 3);
});