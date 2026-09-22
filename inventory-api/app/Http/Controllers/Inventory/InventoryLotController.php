<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryLot\StoreInventoryLotRequest;
use App\Http\Requests\InventoryLot\UpdateInventoryLotRequest;
use App\Http\Resources\InventoryLotResource;
use App\Models\InventoryLot;
use Illuminate\Http\JsonResponse;

class InventoryLotController extends Controller
{
    public function index(): JsonResponse
    {
        $lots = InventoryLot::query()
            ->with('item')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => InventoryLotResource::collection(
                $lots->items()
            ),
            'meta' => [
                'current_page' => $lots->currentPage(),
                'per_page' => $lots->perPage(),
                'total' => $lots->total(),
                'last_page' => $lots->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreInventoryLotRequest $request
    ): JsonResponse {
        $lot = InventoryLot::create(
            $request->validated()
        );

        return response()->json([
            'data' => new InventoryLotResource(
                $lot->load('item')
            ),
            'message' => 'Inventory lot created successfully.',
        ], 201);
    }

    public function show(
        InventoryLot $inventoryLot
    ): JsonResponse {
        return response()->json([
            'data' => new InventoryLotResource(
                $inventoryLot->load('item')
            ),
        ]);
    }

    public function update(
        UpdateInventoryLotRequest $request,
        InventoryLot $inventoryLot
    ): JsonResponse {
        $inventoryLot->update(
            $request->validated()
        );

        return response()->json([
            'data' => new InventoryLotResource(
                $inventoryLot->refresh()->load('item')
            ),
            'message' => 'Inventory lot updated successfully.',
        ]);
    }

    public function destroy(
        InventoryLot $inventoryLot
    ): JsonResponse {
        $inventoryLot->delete();

        return response()->json([
            'message' => 'Inventory lot deleted successfully.',
        ]);
    }
}
