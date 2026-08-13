<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class InventoryTransactionNumberGenerator
{
    private const SEQUENCE = 'inventory_transaction_number_sequence';

    private const PREFIX = 'TRX-';

    public function generate(): string
    {
        $result = DB::selectOne(
            'SELECT nextval(?::regclass) AS value',
            [self::SEQUENCE]
        );

        return sprintf(
            '%s%06d',
            self::PREFIX,
            $result->value
        );
    }
}