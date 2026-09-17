<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventorySerialResource extends JsonResource
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
            'item_id' => $this->item_id,
            'serial_number' => $this->serial_number,
            'status' => $this->status,
            'current_warehouse_id' => $this->current_warehouse_id,
            'current_location_id' => $this->current_location_id,

            'item' => new ItemResource(
                $this->whenLoaded('item')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
