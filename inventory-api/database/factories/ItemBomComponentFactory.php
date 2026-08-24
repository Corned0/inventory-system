<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemBomComponent;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemBomComponent>
 */
class ItemBomComponentFactory extends Factory
{
    protected $model = ItemBomComponent::class;

    public function definition(): array
    {
        return [
            'bom_id' => ItemBom::factory(),
            'component_item_id' => Item::factory(),
            'quantity' => 1,
            'is_required' => true,
        ];
    }

    public function optional(): static
    {
        return $this->state([
            'is_required' => false,
        ]);
    }
}
