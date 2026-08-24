<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemBomResource extends JsonResource
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

            'item' => $this->whenLoaded(
                'item',
                fn () => [
                    'id' => $this->item->id,
                    'code' => $this->item->code,
                    'name' => $this->item->name,
                ]
            ),

            'name' => $this->name,
            'version' => $this->version,
            'is_active' => $this->is_active,

            'components' => ItemBomComponentResource::collection(
                $this->whenLoaded('components')
            ),

            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
