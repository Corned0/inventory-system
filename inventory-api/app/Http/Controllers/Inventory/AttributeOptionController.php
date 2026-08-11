<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeOption\StoreAttributeOptionRequest;
use App\Http\Requests\AttributeOption\UpdateAttributeOptionRequest;
use App\Http\Resources\AttributeOptionResource;
use App\Models\AttributeDefinition;
use App\Models\AttributeOption;
use Illuminate\Http\JsonResponse;

class AttributeOptionController extends Controller
{
    public function index(AttributeDefinition $attribute): JsonResponse
    {
        $options = $attribute->options()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => AttributeOptionResource::collection(
                $options->items()
            ),
            'meta' => [
                'current_page' => $options->currentPage(),
                'per_page' => $options->perPage(),
                'total' => $options->total(),
                'last_page' => $options->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreAttributeOptionRequest $request,
        AttributeDefinition $attribute
    ): JsonResponse {
        $option = $attribute->options()->create(
            $request->validated()
        );

        return response()->json([
            'data' => new AttributeOptionResource($option),
            'message' => 'Attribute option created successfully.',
        ], 201);
    }

    public function show(AttributeOption $option): JsonResponse
    {
        return response()->json([
            'data' => new AttributeOptionResource($option),
        ]);
    }

    public function update(
        UpdateAttributeOptionRequest $request,
        AttributeOption $option
    ): JsonResponse {
        $option->update(
            $request->validated()
        );

        return response()->json([
            'data' => new AttributeOptionResource(
                $option->refresh()
            ),
            'message' => 'Attribute option updated successfully.',
        ]);
    }

    public function destroy(AttributeOption $option): JsonResponse
    {
        $option->delete();

        return response()->json([
            'message' => 'Attribute option deleted successfully.',
        ]);
    }

    public function activate(AttributeOption $option): JsonResponse
    {
        $option->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new AttributeOptionResource(
                $option->refresh()
            ),
            'message' => 'Attribute option activated successfully.',
        ]);
    }

    public function deactivate(AttributeOption $option): JsonResponse
    {
        $option->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new AttributeOptionResource(
                $option->refresh()
            ),
            'message' => 'Attribute option deactivated successfully.',
        ]);
    }
}
