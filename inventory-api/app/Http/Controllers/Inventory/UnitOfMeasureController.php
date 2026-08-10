<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\UnitOfMeasure\StoreUnitOfMeasureRequest;
use App\Http\Requests\UnitOfMeasure\UpdateUnitOfMeasureRequest;
use App\Http\Resources\UnitOfMeasureResource;
use App\Models\UnitOfMeasure;
use Illuminate\Http\JsonResponse;

class UnitOfMeasureController extends Controller
{
    public function index(): JsonResponse
    {
        $units = UnitOfMeasure::query()
            ->orderBy('name')
            ->paginate(20);

        return response()->json([
            'data' => UnitOfMeasureResource::collection(
                $units->items()
            ),
            'meta' => [
                'current_page' => $units->currentPage(),
                'per_page' => $units->perPage(),
                'total' => $units->total(),
                'last_page' => $units->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreUnitOfMeasureRequest $request
    ): JsonResponse {
        $unit = UnitOfMeasure::create(
            $request->validated()
        );

        return response()->json([
            'data' => new UnitOfMeasureResource($unit),
            'message' => 'Unit of measure created successfully.',
        ], 201);
    }

    public function show(UnitOfMeasure $unit): JsonResponse
    {
        return response()->json([
            'data' => new UnitOfMeasureResource($unit),
        ]);
    }

    public function update(
        UpdateUnitOfMeasureRequest $request,
        UnitOfMeasure $unit
    ): JsonResponse {
        $unit->update(
            $request->validated()
        );

        return response()->json([
            'data' => new UnitOfMeasureResource($unit->refresh()),
            'message' => 'Unit of measure updated successfully.',
        ]);
    }

    public function activate(UnitOfMeasure $unit): JsonResponse
    {
        $unit->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new UnitOfMeasureResource($unit->refresh()),
            'message' => 'Unit of measure activated successfully.',
        ]);
    }

    public function deactivate(UnitOfMeasure $unit): JsonResponse
    {
        $unit->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new UnitOfMeasureResource($unit->refresh()),
            'message' => 'Unit of measure deactivated successfully.',
        ]);
    }
}