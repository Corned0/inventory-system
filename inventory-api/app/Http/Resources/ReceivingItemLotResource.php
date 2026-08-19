<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivingItemLotResource extends JsonResource
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
            'receiving_item_id' => $this->receiving_item_id,
            'lot_id' => $this->lot_id,
            'quantity' => $this->quantity,

            'lot' => new InventoryLotResource(
                $this->whenLoaded('lot')
            ),

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
