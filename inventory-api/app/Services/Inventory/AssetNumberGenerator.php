<?php

namespace App\Services\Inventory;

use Illuminate\Support\Facades\DB;

class AssetNumberGenerator
{
    public function generate(): string
    {
        $sequence = DB::selectOne(
            "SELECT nextval('asset_number_sequence') AS number"
        );

        return sprintf(
            'AST-%06d',
            $sequence->number
        );
    }
}