<?php

namespace Database\Factories;

use App\Enums\PutAwayStatus;
use App\Models\PutAway;
use App\Models\Receiving;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Database\Eloquent\Factories\Factory;


/**
 * @extends Factory<PutAway>
 */
class PutAwayFactory extends Factory
{
    protected $model = PutAway::class;

    public function definition(): array
    {
        return [
            'put_away_number' => 'PA-'
                .fake()->unique()->numerify('######'),

            'receiving_id' => Receiving::factory(),

            'warehouse_id' => Warehouse::factory(),

            'status' => PutAwayStatus::Draft,

            'performed_by' => User::factory(),
        ];
    }
}
