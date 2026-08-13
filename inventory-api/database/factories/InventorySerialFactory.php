<?php

namespace Database\Factories;

use App\Enums\InventorySerialStatus;
use App\Models\InventorySerial;
use App\Models\Item;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventorySerial>
 */
class InventorySerialFactory extends Factory
{
    protected $model = InventorySerial::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),
            'serial_number' => fake()->unique()->bothify('SN-########'),
            'status' => InventorySerialStatus::Available,
        ];
    }
}
