<?php

namespace Database\Factories;

use App\Models\AttributeDefinition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttributeDefinition>
 */
class AttributeDefinitionFactory extends Factory
{
    protected $model = AttributeDefinition::class;
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->slug(2),
            'name' => fake()->words(2, true),
            'data_type' => fake()->randomElement([
                'text',
                'textarea',
                'integer',
                'decimal',
                'boolean',
                'date',
                'datetime',
                'select',
                'multiselect',
            ]),
            'description' => fake()->optional()->sentence(),
            'is_required' => false,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
            'validation_rules' => null,
            'default_value' => null,
        ];
    }

    public function select(): static
    {
        return $this->state(fn () => [
            'data_type' => 'select',
        ]);
    }

    public function multiselect(): static
    {
        return $this->state(fn () => [
            'data_type' => 'multiselect',
        ]);
    }

    public function required(): static
    {
        return $this->state(fn () => [
            'is_required' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn () => [
            'is_active' => false,
        ]);
    }
}
