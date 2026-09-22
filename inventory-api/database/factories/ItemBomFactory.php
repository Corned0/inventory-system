<?php

namespace Database\Factories;

use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ItemBom>
 */
class ItemBomFactory extends Factory
{
    protected $model = ItemBom::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory()->state([
                'item_type_id' => ItemType::factory()->create([
                    'is_composite' => true,
                ])->id,
            ]),
            'name' => 'BOM v1',
            'version' => 1,
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state([
            'is_active' => false,
        ]);
    }
}
