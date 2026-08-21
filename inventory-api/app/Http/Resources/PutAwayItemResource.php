<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PutAwayItemResource extends JsonResource
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

            'put_away_id' => $this->put_away_id,

            'receiving_item_id' => $this->receiving_item_id,

            'item_id' => $this->item_id,

            'location_id' => $this->location_id,

            'lot_id' => $this->lot_id,

            'serial_id' => $this->serial_id,

            'quantity' => $this->quantity,

            'item' => $this->whenLoaded(
                'item',
                fn () => new ItemResource($this->item)
            ),

            'location' => $this->whenLoaded(
                'location',
                fn () => new LocationResource($this->location)
            ),

            'lot' => $this->whenLoaded(
                'lot',
                fn () => new InventoryLotResource($this->lot)
            ),

            'serial' => $this->whenLoaded(
                'serial',
                fn () => new InventorySerialResource($this->serial)
            ),
        ];
    }
}
