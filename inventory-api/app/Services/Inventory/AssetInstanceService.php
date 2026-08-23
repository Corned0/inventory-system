<?php

namespace App\Services\Inventory;

use App\Models\AssetInstance;
use Illuminate\Support\Facades\DB;
use App\Enums\AssetInstanceStatus;

class AssetInstanceService
{
    public function __construct(
        private readonly AssetNumberGenerator $assetNumberGenerator
    ) {
    }

    public function create(array $data): AssetInstance
    {
        return DB::transaction(function () use ($data) {

            $status = isset($data['status'])
                ? AssetInstanceStatus::from($data['status'])
                : AssetInstanceStatus::Available;
                
            $assetInstance = AssetInstance::query()->create([
                ...$data,
                'asset_number' => $this->assetNumberGenerator->generate(),
                'status' => $status,
            ]);

            return $assetInstance->load([
                'item',
                'currentWarehouse',
                'currentLocation',
            ]);
        });
    }

    public function update(
        AssetInstance $assetInstance,
        array $data
    ): AssetInstance {
        $assetInstance->update($data);

        return $assetInstance->fresh([
            'item',
            'currentWarehouse',
            'currentLocation',
        ]);
    }
}