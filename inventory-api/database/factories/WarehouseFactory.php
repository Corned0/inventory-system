<?php

namespace Database\Factories;

use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warehouse>
 */
class WarehouseFactory extends Factory
{
    protected $model = Warehouse::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('WH-###'),
            'name' => fake()->company(),
            'description' => fake()->optional()->sentence(),
            'address' => fake()->optional()->address(),
            'is_active' => true,
        ];
    }
}
