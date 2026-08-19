<?php

namespace App\Services\Receiving;

use Illuminate\Support\Facades\DB;

class ReceivingNumberGenerator
{
    public function generate(): string
    {
        $number = DB::selectOne(
            "SELECT nextval('receiving_number_sequence') AS number"
        )->number;

        return sprintf('RCV-%06d', $number);
    }
}