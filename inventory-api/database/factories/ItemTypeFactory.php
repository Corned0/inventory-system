<?php

namespace Database\Factories;

use App\Models\ItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemType>
 */
class ItemTypeFactory extends Factory
{
    protected $model = ItemType::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
         return [
            'code' => fake()->unique()->bothify('TYPE-###'),
            'name' => fake()->words(2, true),
            'description' => fake()->optional()->sentence(),
            'tracking_type' => fake()->randomElement([
                'none',
                'lot',
                'serial',
            ]),
            'is_asset' => fake()->boolean(),
            'is_composite' => fake()->boolean(),
            'is_active' => true,
        ];
    }
}
