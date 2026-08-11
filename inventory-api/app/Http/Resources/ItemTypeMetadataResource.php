<?php

namespace App\Http\Resources;

use App\Models\ItemType;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ItemTypeMetadataResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var ItemType $itemType */
        $itemType = $this->resource;

        return [
            'item_type' => [
                'id' => $itemType->id,
                'code' => $itemType->code,
                'name' => $itemType->name,
            ],

            'attributes' => $itemType->attributes
                ->filter(
                    fn ($itemTypeAttribute) =>
                        $itemTypeAttribute->attributeDefinition !== null
                )
                ->values()
                ->map(function ($itemTypeAttribute): array {
                    $attribute = $itemTypeAttribute->attributeDefinition;

                    return [
                        'id' => $attribute->id,
                        'code' => $attribute->code,
                        'name' => $attribute->name,
                        'data_type' => $attribute->data_type,
                        'required' => $itemTypeAttribute->is_required,
                        'description' => $attribute->description,
                        'default_value' => $attribute->default_value,

                        'options' => in_array(
                            $attribute->data_type,
                            ['select', 'multiselect'],
                            true
                        )
                            ? $attribute->options
                                ->map(fn ($option): array => [
                                    'value' => $option->value,
                                    'label' => $option->label,
                                ])
                                ->values()
                                ->all()
                            : [],
                    ];
                })
                ->all(),
        ];
    }
}
