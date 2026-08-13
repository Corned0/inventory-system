<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Supplier\StoreSupplierRequest;
use App\Http\Requests\Supplier\UpdateSupplierRequest;
use App\Http\Resources\SupplierResource;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;

class SupplierController extends Controller
{
    public function index(): JsonResponse
    {
        $suppliers = Supplier::query()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => SupplierResource::collection(
                $suppliers->items()
            ),
            'meta' => [
                'current_page' => $suppliers->currentPage(),
                'per_page' => $suppliers->perPage(),
                'total' => $suppliers->total(),
                'last_page' => $suppliers->lastPage(),
            ],
        ]);
    }

    public function store(StoreSupplierRequest $request): JsonResponse
    {
        $supplier = Supplier::create(
            $request->validated()
        );

        return response()->json([
            'data' => new SupplierResource($supplier),
            'message' => 'Supplier created successfully.',
        ], 201);
    }

    public function show(Supplier $supplier): JsonResponse
    {
        return response()->json([
            'data' => new SupplierResource($supplier),
        ]);
    }

    public function update(
        UpdateSupplierRequest $request,
        Supplier $supplier
    ): JsonResponse {
        $supplier->update(
            $request->validated()
        );

        return response()->json([
            'data' => new SupplierResource(
                $supplier->refresh()
            ),
            'message' => 'Supplier updated successfully.',
        ]);
    }
}
