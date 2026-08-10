<?php

use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Traits\ActsAsAdmin;

uses(
    RefreshDatabase::class,
    ActsAsAdmin::class,
);

beforeEach(function () {
    $this->actingAsAdmin();
});

/*
|--------------------------------------------------------------------------
| Index
|--------------------------------------------------------------------------
*/

it('returns paginated units of measure', function () {
    UnitOfMeasure::factory()->count(10)->create();

    $response = $this->getJson(route('units.index'));

    $response
        ->assertOk()
        ->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'code',
                    'name',
                    'symbol',
                    'decimal_places',
                    'is_active',
                ],
            ],
            'meta' => [
                'current_page',
                'per_page',
                'total',
                'last_page',
            ],
        ]);
});

it('returns an empty list when there are no units', function () {
    $response = $this->getJson(route('units.index'));

    $response
        ->assertOk()
        ->assertJson([
            'data' => [],
            'meta' => [
                'total' => 0,
            ],
        ]);
});

/*
|--------------------------------------------------------------------------
| Store
|--------------------------------------------------------------------------
*/

it('creates a unit of measure', function () {
    $payload = [
        'code' => 'PC',
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
        'is_active' => true,
    ];

    $response = $this->postJson(
        route('units.store'),
        $payload
    );

    $response
        ->assertCreated()
        ->assertJson([
            'message' => 'Unit of measure created successfully.',
        ])
        ->assertJsonPath('data.code', 'PC')
        ->assertJsonPath('data.name', 'Piece')
        ->assertJsonPath('data.symbol', 'pc')
        ->assertJsonPath('data.decimal_places', 0)
        ->assertJsonPath('data.is_active', true);

    $this->assertDatabaseHas('units_of_measure', [
        'code' => 'PC',
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
        'is_active' => true,
    ]);
});

it('requires code when creating a unit', function () {
    $payload = [
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
    ];

    $this->postJson(route('units.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

it('requires name when creating a unit', function () {
    $payload = [
        'code' => 'PC',
        'symbol' => 'pc',
        'decimal_places' => 0,
    ];

    $this->postJson(route('units.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('name');
});

it('requires symbol when creating a unit', function () {
    $payload = [
        'code' => 'PC',
        'name' => 'Piece',
        'decimal_places' => 0,
    ];

    $this->postJson(route('units.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('symbol');
});

it('requires unique code when creating a unit', function () {
    UnitOfMeasure::factory()->create([
        'code' => 'PC',
    ]);

    $payload = [
        'code' => 'PC',
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
    ];

    $this->postJson(route('units.store'), $payload)
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

/*
|--------------------------------------------------------------------------
| Show
|--------------------------------------------------------------------------
*/

it('returns a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create([
        'code' => 'KG',
        'name' => 'Kilogram',
        'symbol' => 'kg',
        'decimal_places' => 3,
    ]);

    $response = $this->getJson(
        route('units.show', $unit)
    );

    $response
        ->assertOk()
        ->assertJsonPath('data.id', $unit->id)
        ->assertJsonPath('data.code', 'KG')
        ->assertJsonPath('data.name', 'Kilogram')
        ->assertJsonPath('data.symbol', 'kg')
        ->assertJsonPath('data.decimal_places', 3);
});

it('returns 404 when unit does not exist', function () {
    $this->getJson(
        route('units.show', 999999)
    )->assertNotFound();
});

/*
|--------------------------------------------------------------------------
| Update
|--------------------------------------------------------------------------
*/

it('updates a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create([
        'code' => 'PC',
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
    ]);

    $payload = [
        'name' => 'Pieces',
        'symbol' => 'pcs',
        'decimal_places' => 2,
    ];

    $response = $this->patchJson(
        route('units.update', $unit),
        $payload
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Unit of measure updated successfully.',
        ]);

    $this->assertDatabaseHas('units_of_measure', [
        'id' => $unit->id,
        'code' => 'PC',
        'name' => 'Pieces',
        'symbol' => 'pcs',
        'decimal_places' => 2,
    ]);
});

it('cannot update a unit with an existing code', function () {
    UnitOfMeasure::factory()->create([
        'code' => 'PC',
    ]);

    $unit = UnitOfMeasure::factory()->create([
        'code' => 'BOX',
    ]);

    $this->patchJson(
        route('units.update', $unit),
        [
            'code' => 'PC',
        ]
    )
        ->assertUnprocessable()
        ->assertJsonValidationErrors('code');
});

/*
|--------------------------------------------------------------------------
| Activate
|--------------------------------------------------------------------------
*/

it('activates a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create([
        'is_active' => false,
    ]);

    $response = $this->postJson(
        route('units.activate', $unit)
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Unit of measure activated successfully.',
        ]);

    $this->assertDatabaseHas('units_of_measure', [
        'id' => $unit->id,
        'is_active' => true,
    ]);
});

/*
|--------------------------------------------------------------------------
| Deactivate
|--------------------------------------------------------------------------
*/

it('deactivates a unit of measure', function () {
    $unit = UnitOfMeasure::factory()->create([
        'is_active' => true,
    ]);

    $response = $this->postJson(
        route('units.deactivate', $unit)
    );

    $response
        ->assertOk()
        ->assertJson([
            'message' => 'Unit of measure deactivated successfully.',
        ]);

    $this->assertDatabaseHas('units_of_measure', [
        'id' => $unit->id,
        'is_active' => false,
    ]);
});

/*
|--------------------------------------------------------------------------
| Authentication
|--------------------------------------------------------------------------
*/

it('rejects guest requests', function () {
    auth()->forgetGuards();

    $this->getJson(route('units.index'))
        ->assertUnauthorized();
});

it('rejects guest unit creation', function () {
    auth()->forgetGuards();

    $this->postJson(route('units.store'), [
        'code' => 'PC',
        'name' => 'Piece',
        'symbol' => 'pc',
        'decimal_places' => 0,
    ])->assertUnauthorized();
});