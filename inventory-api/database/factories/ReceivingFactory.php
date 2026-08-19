<?php

namespace Database\Factories;

use App\Enums\ReceivingStatus;
use App\Models\Receiving;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Receiving>
 */
class ReceivingFactory extends Factory
{
    protected $model = Receiving::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receiving_number' => fake()->unique()->numerify('RCV-######'),
            'supplier_id' => Supplier::factory(),
            'purchase_order_id' => null,
            'warehouse_id' => Warehouse::factory(),
            'received_date' => now(),
            'status' => ReceivingStatus::Draft,
            'received_by' => null,
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
