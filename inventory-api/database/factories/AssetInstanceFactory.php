<?php

namespace Database\Factories;

use App\Enums\AssetInstanceStatus;
use App\Models\AssetInstance;
use App\Models\Item;
use App\Models\Location;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AssetInstance>
 */
class AssetInstanceFactory extends Factory
{
    protected $model = AssetInstance::class;

    public function definition(): array
    {
        return [
            'item_id' => Item::factory(),

            'asset_number' => 'AST-'.fake()->unique()->numerify('######'),

            'serial_number' => fake()
                ->unique()
                ->bothify('SN-##########'),

            'status' => AssetInstanceStatus::Available,

            'current_warehouse_id' => Warehouse::factory(),

            'current_location_id' => null,

            'acquired_at' => now(),

            'acquisition_cost' => 50000,

            'warranty_start' => now()->toDateString(),

            'warranty_end' => now()
                ->addYears(3)
                ->toDateString(),
        ];
    }

    public function withoutLocation(): static
    {
        return $this->state(fn () => [
            'current_location_id' => null,
        ]);
    }

    public function disposed(): static
    {
        return $this->state(fn () => [
            'status' => AssetInstanceStatus::Disposed,
        ]);
    }
}
