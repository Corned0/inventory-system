<?php

namespace Database\Factories;

use App\Models\AttributeDefinition;
use App\Models\ItemType;
use App\Models\ItemTypeAttribute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemTypeAttribute>
 */
class ItemTypeAttributeFactory extends Factory
{
    protected $model = ItemTypeAttribute::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'item_type_id' => ItemType::factory(),
            'attribute_definition_id' => AttributeDefinition::factory(),
            'is_required' => false,
            'sort_order' => fake()->numberBetween(0, 10),
        ];
    }

    public function required(): static
    {
        return $this->state(fn () => [
            'is_required' => true,
        ]);
    }
}
