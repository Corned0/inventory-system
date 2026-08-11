<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Resources\ItemTypeMetadataResource;
use App\Models\ItemType;
use Illuminate\Http\JsonResponse;

class ItemTypeMetadataController extends Controller
{
    public function show(ItemType $itemType): JsonResponse
    {
        $itemType->load([
            'attributes' => fn ($query) => $query
                ->with([
                    'attributeDefinition' => fn ($query) => $query
                        ->where('is_active', true)
                        ->with([
                            'options' => fn ($query) => $query
                                ->where('is_active', true)
                                ->orderBy('sort_order'),
                        ]),
                ])
                ->orderBy('sort_order'),
        ]);

        return response()->json([
            'data' => new ItemTypeMetadataResource($itemType),
        ]);
    }
}
