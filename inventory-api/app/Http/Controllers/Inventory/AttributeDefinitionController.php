<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttributeDefinition\StoreAttributeDefinitionRequest;
use App\Http\Requests\AttributeDefinition\UpdateAttributeDefinitionRequest;
use App\Http\Resources\AttributeDefinitionResource;
use App\Models\AttributeDefinition;
use Illuminate\Http\JsonResponse;

class AttributeDefinitionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $attributes = AttributeDefinition::query()
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => AttributeDefinitionResource::collection(
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

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreAttributeDefinitionRequest $request): JsonResponse
    {
        $attribute = AttributeDefinition::create(
            $request->validated()
        );

        return response()->json([
            'data' => new AttributeDefinitionResource($attribute),
            'message' => 'Attribute created successfully.',
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(AttributeDefinition $attribute):JsonResponse 
    {
        return response()->json([
            'data' => new AttributeDefinitionResource(
                $attribute->load('options')
            ),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateAttributeDefinitionRequest $request, AttributeDefinition $attribute): JsonResponse {
        
        $attribute->update($request->validated());

        return response()->json([
            'data' => new AttributeDefinitionResource(
                $attribute->refresh()
            ),
            'message' => 'Attribute updated successfully.',
        ]);
    }

    public function activate(AttributeDefinition $attribute): JsonResponse {
        
        $attribute->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new AttributeDefinitionResource(
                $attribute->refresh()
            ),
            'message' => 'Attribute activated successfully.',
        ]);
    }

    public function deactivate(AttributeDefinition $attribute): JsonResponse {
        
        $attribute->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new AttributeDefinitionResource(
                $attribute->refresh()
            ),
            'message' => 'Attribute deactivated successfully.',
        ]);
    }
}
