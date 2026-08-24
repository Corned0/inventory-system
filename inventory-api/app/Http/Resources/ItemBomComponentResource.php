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

            'component_item' => $this->whenLoaded(
                'componentItem',
                fn () => [
                    'id' => $this->componentItem->id,
                    'code' => $this->componentItem->code,
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
