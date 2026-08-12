<?php

use App\Services\Inventory\ItemCodeGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

uses(RefreshDatabase::class);

beforeEach(function () {
    DB::statement("ALTER SEQUENCE item_code_sequence RESTART WITH 1");
});

it('generates an item code', function () {
    $generator = app(ItemCodeGenerator::class);

    expect($generator->generate())
        ->toBe('ITM-000001');
});

it('generates sequential item codes', function () {
    $generator = app(ItemCodeGenerator::class);

    expect($generator->generate())
        ->toBe('ITM-000001');

    expect($generator->generate())
        ->toBe('ITM-000002');

    expect($generator->generate())
        ->toBe('ITM-000003');
});

it('generates item codes with the correct format', function () {
    $generator = app(ItemCodeGenerator::class);

    $code = $generator->generate();

    expect($code)
        ->toMatch('/^ITM-\d{6}$/');
});