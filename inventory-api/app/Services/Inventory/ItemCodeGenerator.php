<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class ItemCodeGenerator
{
    private const SEQUENCE = 'item_code_sequence';

    public function generate(): string
    {
        $result = DB::selectOne(
            "SELECT nextval('" . self::SEQUENCE . "') AS number"
        );

        return sprintf('ITM-%06d', $result->number);
    }
}