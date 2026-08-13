<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InventoryTransactionResource extends JsonResource
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
            'transaction_number' => $this->transaction_number,
            'transaction_type' => $this->transaction_type,

            'item_id' => $this->item_id,
            'warehouse_id' => $this->warehouse_id,
            'location_id' => $this->location_id,
            'lot_id' => $this->lot_id,
            'serial_id' => $this->serial_id,

            'quantity' => $this->quantity,
            'unit_cost' => $this->unit_cost,

            'reference_type' => $this->reference_type,
            'reference_id' => $this->reference_id,

            'transaction_date' => $this->transaction_date,

            'performed_by' => $this->performed_by,

            'remarks' => $this->remarks,

            'item' => new ItemResource(
                $this->whenLoaded('item')
            ),

            'warehouse' => new WarehouseResource(
                $this->whenLoaded('warehouse')
            ),

            'location' => new LocationResource(
                $this->whenLoaded('location')
            ),

            'lot' => new InventoryLotResource(
                $this->whenLoaded('lot')
            ),

            'serial' => new InventorySerialResource(
                $this->whenLoaded('serial')
            ),

            'created_at' => $this->created_at,
        ];
    }
}
