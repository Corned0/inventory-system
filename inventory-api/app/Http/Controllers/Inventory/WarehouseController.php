<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Warehouse\StoreWarehouseRequest;
use App\Http\Requests\Warehouse\UpdateWarehouseRequest;
use App\Http\Resources\WarehouseResource;
use App\Models\Warehouse;
use Illuminate\Http\JsonResponse;

class WarehouseController extends Controller
{
    public function index(): JsonResponse
    {
        $warehouses = Warehouse::query()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => WarehouseResource::collection(
                $warehouses->items()
            ),
            'meta' => [
                'current_page' => $warehouses->currentPage(),
                'per_page' => $warehouses->perPage(),
                'total' => $warehouses->total(),
                'last_page' => $warehouses->lastPage(),
            ],
        ]);
    }

    public function store(StoreWarehouseRequest $request): JsonResponse
    {
        $warehouse = Warehouse::create(
            $request->validated()
        );

        return response()->json([
            'data' => new WarehouseResource($warehouse),
            'message' => 'Warehouse created successfully.',
        ], 201);
    }

    public function show(Warehouse $warehouse): JsonResponse
    {
        return response()->json([
            'data' => new WarehouseResource($warehouse),
        ]);
    }

    public function update(
        UpdateWarehouseRequest $request,
        Warehouse $warehouse
    ): JsonResponse {
        $warehouse->update(
            $request->validated()
        );

        return response()->json([
            'data' => new WarehouseResource(
                $warehouse->refresh()
            ),
            'message' => 'Warehouse updated successfully.',
        ]);
    }
}
