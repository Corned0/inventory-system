<?php

use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

it('returns paginated warehouses', function () {
    Warehouse::factory()->count(3)->create();

    $response = $this->getJson(
        route('warehouses.index')
    );

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'description',
                    'address',
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

it('returns an empty list when there are no warehouses', function () {
    $response = $this->getJson(
        route('warehouses.index')
    );

    $response
        ->assertOk()
        ->assertJsonPath('data', [])
        ->assertJsonPath('meta.total', 0);
});