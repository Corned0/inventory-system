<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivingItemResource extends JsonResource
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
            'receiving_id' => $this->receiving_id,
            'item_id' => $this->item_id,

            'ordered_quantity' => $this->ordered_quantity,
            'received_quantity' => $this->received_quantity,
            'accepted_quantity' => $this->accepted_quantity,
            'rejected_quantity' => $this->rejected_quantity,
            'unit_cost' => $this->unit_cost,

            'item' => $this->whenLoaded(
                'item',
                fn () => [
                    'id' => $this->item->id,
                    'code' => $this->item->code,
                    'name' => $this->item->name,
                ]
            ),

            'lots' => ReceivingItemLotResource::collection(
                $this->whenLoaded('lots')
            ),

            'serials' => ReceivingItemSerialResource::collection(
                $this->whenLoaded('serials')
            ),
        ];
    }
}
