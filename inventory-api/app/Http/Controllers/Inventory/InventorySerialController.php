<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventorySerial\StoreInventorySerialRequest;
use App\Http\Requests\InventorySerial\UpdateInventorySerialRequest;
use App\Http\Resources\InventorySerialResource;
use App\Models\InventorySerial;
use App\Enums\InventorySerialStatus;
use Illuminate\Http\JsonResponse;

class InventorySerialController extends Controller
{
    public function index(): JsonResponse
    {
        $serials = InventorySerial::query()
            ->with('item')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => InventorySerialResource::collection(
                $serials->items()
            ),
            'meta' => [
                'current_page' => $serials->currentPage(),
                'per_page' => $serials->perPage(),
                'total' => $serials->total(),
                'last_page' => $serials->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreInventorySerialRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $data['status'] = InventorySerialStatus::Available;

        $serial = InventorySerial::create($data);

        return response()->json([
            'data' => new InventorySerialResource(
                $serial->load('item')
            ),
            'message' => 'Inventory serial created successfully.',
        ], 201);
    }

    public function show(
        InventorySerial $inventorySerial
    ): JsonResponse {
        return response()->json([
            'data' => new InventorySerialResource(
                $inventorySerial->load('item')
            ),
        ]);
    }

    public function update(
        UpdateInventorySerialRequest $request,
        InventorySerial $inventorySerial
    ): JsonResponse {
        $inventorySerial->update(
            $request->validated()
        );

        return response()->json([
            'data' => new InventorySerialResource(
                $inventorySerial->refresh()->load('item')
            ),
            'message' => 'Inventory serial updated successfully.',
        ]);
    }
}
