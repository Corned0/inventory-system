<?php

namespace Database\Factories;

use App\Models\InventoryBalance;
use App\Models\Item;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryBalance>
 */
class InventoryBalanceFactory extends Factory
{
    protected $model = InventoryBalance::class;

    public function definition(): array
    {
        $quantity = fake()->randomFloat(
            4,
            1,
            1000
        );

        $reserved = 0;

        return [
            'item_id' => Item::factory(),
            'warehouse_id' => Warehouse::factory(),
            'location_id' => null,
            'lot_id' => null,
            'quantity' => $quantity,
            'reserved_quantity' => $reserved,
            'available_quantity' => $quantity,
        ];
    }
}