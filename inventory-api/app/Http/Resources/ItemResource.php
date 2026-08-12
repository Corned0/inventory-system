<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemResource extends JsonResource
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
            'item_code' => $this->item_code,
            'barcode' => $this->barcode,
            'name' => $this->name,
            'description' => $this->description,

            'category' => $this->whenLoaded('category', fn () => [
                'id' => $this->category->id,
                'code' => $this->category->code,
                'name' => $this->category->name,
            ]),

            'item_type' => $this->whenLoaded('itemType', fn () => [
                'id' => $this->itemType->id,
                'code' => $this->itemType->code,
                'name' => $this->itemType->name,
                'tracking_type' => $this->itemType->tracking_type,
            ]),

            'unit' => $this->whenLoaded('unitOfMeasure', fn () => [
                'id' => $this->unitOfMeasure->id,
                'code' => $this->unitOfMeasure->code,
                'name' => $this->unitOfMeasure->name,
            ]),

            'reorder_level' => $this->reorder_level,
            'reorder_quantity' => $this->reorder_quantity,
            'is_active' => $this->is_active,

            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
