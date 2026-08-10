<?php

namespace Database\Seeders;

use App\Models\UnitOfMeasure;
use Illuminate\Database\Seeder;

class UnitOfMeasureSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            [
                'code' => 'PC',
                'name' => 'Piece',
                'symbol' => 'pc',
                'decimal_places' => 0,
            ],
            [
                'code' => 'BOX',
                'name' => 'Box',
                'symbol' => 'box',
                'decimal_places' => 0,
            ],
            [
                'code' => 'REAM',
                'name' => 'Ream',
                'symbol' => 'ream',
                'decimal_places' => 0,
            ],
            [
                'code' => 'KG',
                'name' => 'Kilogram',
                'symbol' => 'kg',
                'decimal_places' => 2,
            ],
            [
                'code' => 'L',
                'name' => 'Liter',
                'symbol' => 'L',
                'decimal_places' => 2,
            ],
            [
                'code' => 'SET',
                'name' => 'Set',
                'symbol' => 'set',
                'decimal_places' => 0,
            ],
        ];

        foreach ($units as $unit) {
            UnitOfMeasure::updateOrCreate(
                ['code' => $unit['code']],
                [
                    ...$unit,
                    'is_active' => true,
                ],
            );
        }
    }
}