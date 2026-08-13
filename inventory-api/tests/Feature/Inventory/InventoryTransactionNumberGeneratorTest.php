<?php

use App\Services\Inventory\InventoryTransactionNumberGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {

    DB::statement(
        'ALTER SEQUENCE inventory_transaction_number_sequence RESTART WITH 1'
    );
});

it('generates sequential inventory transaction numbers', function () {
    $generator = app(InventoryTransactionNumberGenerator::class);

    expect($generator->generate())
        ->toBe('TRX-000001');

    expect($generator->generate())
        ->toBe('TRX-000002');

    expect($generator->generate())
        ->toBe('TRX-000003');
});

it('generates unique transaction numbers', function () {
    $generator = app(InventoryTransactionNumberGenerator::class);

    $first = $generator->generate();
    $second = $generator->generate();

    expect($first)
        ->not->toBe($second);
});