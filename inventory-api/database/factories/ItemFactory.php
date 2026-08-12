<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemCategory;
use App\Models\ItemType;
use App\Models\UnitOfMeasure;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Item>
 */
class ItemFactory extends Factory
{
    protected $model = Item::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_code' => 'ITM-' . fake()->unique()->numerify('######'),
            'barcode' => fake()->unique()->numerify('############'),
            'name' => fake()->words(3, true),
            'description' => fake()->optional()->sentence(),

            'category_id' => ItemCategory::factory(),
            'item_type_id' => ItemType::factory(),
            'unit_of_measure_id' => UnitOfMeasure::factory(),

            'reorder_level' => fake()->numberBetween(0, 10),
            'reorder_quantity' => fake()->numberBetween(1, 50),

            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }

    public function withoutBarcode(): static
    {
        return $this->state(fn () => [
            'barcode' => null,
        ]);
    }
}
