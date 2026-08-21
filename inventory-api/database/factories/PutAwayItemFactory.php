<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Location;
use App\Models\PutAway;
use App\Models\PutAwayItem;
use App\Models\ReceivingItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class PutAwayItemFactory extends Factory
{
    protected $model = PutAwayItem::class;

    public function definition(): array
    {
        return [
            'put_away_id' => PutAway::factory(),

            'receiving_item_id' => ReceivingItem::factory(),

            'item_id' => Item::factory(),

            'location_id' => Location::factory(),

            'lot_id' => null,

            'serial_id' => null,

            'quantity' => fake()->randomFloat(
                4,
                1,
                100
            ),
        ];
    }
}