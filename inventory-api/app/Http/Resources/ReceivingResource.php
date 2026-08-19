<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReceivingResource extends JsonResource
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
            'receiving_number' => $this->receiving_number,
            'supplier_id' => $this->supplier_id,
            'purchase_order_id' => $this->purchase_order_id,
            'warehouse_id' => $this->warehouse_id,
            'received_date' => $this->received_date?->toISOString(),
            'status' => $this->status?->value,
            'received_by' => $this->received_by,
            'remarks' => $this->remarks,

            'supplier' => $this->whenLoaded(
                'supplier',
                fn () => [
                    'id' => $this->supplier->id,
                    'name' => $this->supplier->name,
                ]
            ),

            'warehouse' => $this->whenLoaded(
                'warehouse',
                fn () => [
                    'id' => $this->warehouse->id,
                    'name' => $this->warehouse->name,
                ]
            ),

            'items' => ReceivingItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
