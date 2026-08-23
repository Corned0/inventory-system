<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AssetInstanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'asset_number' => $this->asset_number,
            'serial_number' => $this->serial_number,
            'status' => $this->status?->value,

            'item' => $this->whenLoaded(
                'item',
                fn () => [
                    'id' => $this->item->id,
                    'code' => $this->item->code,
                    'name' => $this->item->name,
                ]
            ),

            'current_warehouse' => $this->whenLoaded(
                'currentWarehouse',
                fn () => $this->currentWarehouse
                    ? [
                        'id' => $this->currentWarehouse->id,
                        'name' => $this->currentWarehouse->name,
                    ]
                    : null
            ),

            'current_location' => $this->whenLoaded(
                'currentLocation',
                fn () => $this->currentLocation
                    ? [
                        'id' => $this->currentLocation->id,
                        'name' => $this->currentLocation->name,
                    ]
                    : null
            ),

            'acquired_at' => $this->acquired_at?->toISOString(),

            'acquisition_cost' => $this->acquisition_cost,

            'warranty_start' => $this->warranty_start?->toDateString(),

            'warranty_end' => $this->warranty_end?->toDateString(),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
