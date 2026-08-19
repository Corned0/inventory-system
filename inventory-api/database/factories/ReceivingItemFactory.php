<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ReceivingItem>
 */
class ReceivingItemFactory extends Factory
{
    protected $model = ReceivingItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'receiving_id' => Receiving::factory(),
            'item_id' => Item::factory(),
            'ordered_quantity' => 10,
            'received_quantity' => 10,
            'accepted_quantity' => 0,
            'rejected_quantity' => 0,
            'unit_cost' => 1000,
        ];
    }
}
