<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemTypeAttribute\StoreItemTypeAttributeRequest;
use App\Http\Requests\ItemTypeAttribute\UpdateItemTypeAttributeRequest;
use App\Http\Resources\ItemTypeAttributeResource;
use App\Models\ItemType;
use App\Models\ItemTypeAttribute;
use Illuminate\Http\JsonResponse;

class ItemTypeAttributeController extends Controller
{
    public function index(ItemType $itemType): JsonResponse
    {
        $attributes = $itemType->attributes()
            ->with('attributeDefinition')
            ->orderBy('sort_order')
            ->paginate(20);

        return response()->json([
            'data' => ItemTypeAttributeResource::collection(
                $attributes->items()
            ),
            'meta' => [
                'current_page' => $attributes->currentPage(),
                'per_page' => $attributes->perPage(),
                'total' => $attributes->total(),
                'last_page' => $attributes->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreItemTypeAttributeRequest $request,
        ItemType $itemType
    ): JsonResponse {
        $itemTypeAttribute = ItemTypeAttribute::create([
            'item_type_id' => $itemType->id,
            ...$request->validated(),
        ]);

        $itemTypeAttribute->load('attributeDefinition');

        return response()->json([
            'data' => new ItemTypeAttributeResource($itemTypeAttribute),
            'message' => 'Attribute assigned successfully.',
        ], 201);
    }

    public function update(
        UpdateItemTypeAttributeRequest $request,
        ItemType $itemType,
        ItemTypeAttribute $itemTypeAttribute
    ): JsonResponse {
        $this->ensureBelongsToItemType(
            $itemTypeAttribute,
            $itemType
        );

        $itemTypeAttribute->update(
            $request->validated()
        );

        return response()->json([
            'data' => new ItemTypeAttributeResource(
                $itemTypeAttribute
                    ->refresh()
                    ->load('attributeDefinition')
            ),
            'message' => 'Item type attribute updated successfully.',
        ]);
    }

    public function destroy(
        ItemType $itemType,
        ItemTypeAttribute $itemTypeAttribute
    ): JsonResponse {
        $this->ensureBelongsToItemType(
            $itemTypeAttribute,
            $itemType
        );

        $itemTypeAttribute->delete();

        return response()->json([
            'message' => 'Attribute removed successfully.',
        ]);
    }

    private function ensureBelongsToItemType(
        ItemTypeAttribute $itemTypeAttribute,
        ItemType $itemType
    ): void {
        abort_unless(
            $itemTypeAttribute->item_type_id === $itemType->id,
            404
        );
    }
}
