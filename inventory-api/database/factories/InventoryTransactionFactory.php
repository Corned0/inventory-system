<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Models\Warehouse;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryTransaction>
 */
class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'transaction_number' => fake()->unique()->numerify('TRX-######'),
            'transaction_type' => 'receipt',
            'item_id' => Item::factory(),
            'warehouse_id' => Warehouse::factory(),
            'location_id' => null,
            'lot_id' => null,
            'serial_id' => null,
            'quantity' => 10,
            'unit_cost' => 1000,
            'reference_type' => null,
            'reference_id' => null,
            'transaction_date' => now(),
            'performed_by' => User::factory(),
            'remarks' => null,
        ];
    }
}
