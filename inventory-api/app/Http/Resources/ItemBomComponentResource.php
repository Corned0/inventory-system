<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemBomComponentResource extends JsonResource
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
            'bom_id' => $this->bom_id,
            'component_item_id' => $this->component_item_id,

            'component_item' => $this->whenLoaded(
                'componentItem',
                fn () => [
                    'id' => $this->componentItem->id,
                    'item_code' => $this->componentItem->item_code,
                    'name' => $this->componentItem->name,
                ]
            ),

            'quantity' => $this->quantity,
            'is_required' => $this->is_required,

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
