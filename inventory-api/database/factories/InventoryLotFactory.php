<?php

namespace Database\Factories;

use App\Models\InventoryLot;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLot>
 */
class InventoryLotFactory extends Factory
{
    protected $model = InventoryLot::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $manufacturedDate = fake()->dateTimeBetween(
            '-2 years',
            'now'
        );

        return [
            'item_id' => Item::factory(),
            'lot_number' => fake()->unique()->bothify('LOT-####-????'),
            'manufactured_date' => $manufacturedDate,
            'expiration_date' => fake()->dateTimeBetween(
                $manufacturedDate,
                '+3 years'
            ),
        ];
    }
}
