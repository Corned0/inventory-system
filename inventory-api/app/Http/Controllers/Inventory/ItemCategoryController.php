<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemCategory\StoreItemCategoryRequest;
use App\Http\Requests\ItemCategory\UpdateItemCategoryRequest;
use App\Http\Resources\ItemCategoryResource;
use App\Models\ItemCategory;
use Illuminate\Http\JsonResponse;

class ItemCategoryController extends Controller
{
    public function index(): JsonResponse
    {
        $categories = ItemCategory::query()
            ->with('parent')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => ItemCategoryResource::collection($categories->items()),
            'meta' => [
                'current_page' => $categories->currentPage(),
                'per_page' => $categories->perPage(),
                'total' => $categories->total(),
                'last_page' => $categories->lastPage(),
            ],
        ]);
    }

    public function tree(): JsonResponse
    {
        $categories = ItemCategory::query()
            ->orderBy('name')
            ->get();

        $tree = $this->buildTree($categories);

        return response()->json([
            'data' => $tree,
        ]);
    }

    public function store(StoreItemCategoryRequest $request): JsonResponse {
        $category = ItemCategory::create(
            $request->validated()
        );

        return response()->json([
            'data' => new ItemCategoryResource($category),
            'message' => 'Category created successfully.',
        ], 201);
    }

    public function show(ItemCategory $category): JsonResponse
    {
        return response()->json([
            'data' => new ItemCategoryResource(
                $category->load('parent', 'children')
            ),
        ]);
    }

    public function update(
        UpdateItemCategoryRequest $request,
        ItemCategory $category
    ): JsonResponse {
        $category->update(
            $request->validated()
        );

        return response()->json([
            'data' => new ItemCategoryResource(
                $category->refresh()
            ),
            'message' => 'Category updated successfully.',
        ]);
    }

    public function destroy(ItemCategory $category): JsonResponse
    {
        if ($category->children()->exists()) {
            return response()->json([
                'message' => 'Category cannot be deleted because it has child categories.',
            ], 409);
        }

        $category->delete();

        return response()->json([
            'message' => 'Category deleted successfully.',
        ]);
    }

    public function activate(ItemCategory $category): JsonResponse
    {
        $category->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new ItemCategoryResource($category->refresh()),
            'message' => 'Category activated successfully.',
        ]);
    }

    public function deactivate(ItemCategory $category): JsonResponse
    {
        $category->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new ItemCategoryResource($category->refresh()),
            'message' => 'Category deactivated successfully.',
        ]);
    }

    private function buildTree(
        \Illuminate\Support\Collection $categories,
        ?int $parentId = null
    ): array {
        return $categories
            ->where('parent_id', $parentId)
            ->map(function (ItemCategory $category) use ($categories): array {
                return [
                    'id' => $category->id,
                    'parent_id' => $category->parent_id,
                    'code' => $category->code,
                    'name' => $category->name,
                    'description' => $category->description,
                    'is_active' => $category->is_active,
                    'children' => $this->buildTree(
                        $categories,
                        $category->id
                    ),
                ];
            })
            ->values()
            ->all();
    }
}
