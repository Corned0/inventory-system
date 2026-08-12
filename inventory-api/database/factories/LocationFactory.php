<?php

namespace Database\Factories;

use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Location>
 */
class LocationFactory extends Factory
{
    protected $model = Location::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'warehouse_id' => Warehouse::factory(),
            'parent_id' => null,
            'code' => fake()->unique()->bothify('LOC-###'),
            'name' => fake()->words(2, true),
            'location_type' => fake()->randomElement([
                'warehouse',
                'building',
                'room',
                'rack',
                'shelf',
                'bin',
            ]),
            'is_active' => true,
        ];
    }
}
