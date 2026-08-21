<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PutAwayResource extends JsonResource
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

            'put_away_number' => $this->put_away_number,

            'receiving_id' => $this->receiving_id,

            'warehouse_id' => $this->warehouse_id,

            'status' => $this->status?->value,

            'performed_by' => $this->performed_by,

            'receiving' => $this->whenLoaded(
                'receiving',
                fn () => new ReceivingResource(
                    $this->receiving
                )
            ),

            'warehouse' => $this->whenLoaded(
                'warehouse',
                fn () => new WarehouseResource(
                    $this->warehouse
                )
            ),

            'items' => PutAwayItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => $this->created_at?->toISOString(),

            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
