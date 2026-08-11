<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemType\StoreItemTypeRequest;
use App\Http\Requests\ItemType\UpdateItemTypeRequest;
use App\Http\Resources\ItemTypeResource;
use App\Models\ItemType;
use Illuminate\Http\JsonResponse;

class ItemTypeController extends Controller
{
    public function index(): JsonResponse
    {
        $itemTypes = ItemType::query()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => ItemTypeResource::collection(
                $itemTypes->items()
            ),
            'meta' => [
                'current_page' => $itemTypes->currentPage(),
                'per_page' => $itemTypes->perPage(),
                'total' => $itemTypes->total(),
                'last_page' => $itemTypes->lastPage(),
            ],
        ]);
    }

    public function store(StoreItemTypeRequest $request): JsonResponse
    {
        $itemType = ItemType::create(
            $request->validated()
        );

        return response()->json([
            'data' => new ItemTypeResource($itemType),
            'message' => 'Item type created successfully.',
        ], 201);
    }

    public function show(ItemType $itemType): JsonResponse
    {
        return response()->json([
            'data' => new ItemTypeResource($itemType),
        ]);
    }

    public function update(
        UpdateItemTypeRequest $request,
        ItemType $itemType
    ): JsonResponse {
        $itemType->update(
            $request->validated()
        );

        return response()->json([
            'data' => new ItemTypeResource(
                $itemType->refresh()
            ),
            'message' => 'Item type updated successfully.',
        ]);
    }

    public function activate(ItemType $itemType): JsonResponse
    {
        $itemType->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new ItemTypeResource(
                $itemType->refresh()
            ),
            'message' => 'Item type activated successfully.',
        ]);
    }

    public function deactivate(ItemType $itemType): JsonResponse
    {
        $itemType->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new ItemTypeResource(
                $itemType->refresh()
            ),
            'message' => 'Item type deactivated successfully.',
        ]);
    }
}
