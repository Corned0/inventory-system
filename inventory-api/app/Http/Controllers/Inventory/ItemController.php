<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Item\StoreItemRequest;
use App\Http\Requests\Item\UpdateItemRequest;
use App\Http\Resources\ItemResource;
use App\Models\Item;
use App\Services\Inventory\ItemAttributeValueService;
use App\Services\Inventory\ItemCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function __construct(
        private readonly ItemCodeGenerator $itemCodeGenerator,
        private readonly ItemAttributeValueService $attributeValueService,
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $items = Item::query()
            ->with([
                'category',
                'itemType',
                'unitOfMeasure',
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => ItemResource::collection(
                $items->items()
            ),
            'meta' => [
                'current_page' => $items->currentPage(),
                'per_page' => $items->perPage(),
                'total' => $items->total(),
                'last_page' => $items->lastPage(),
            ],
        ]);
    }

    public function store(StoreItemRequest $request): JsonResponse
    {
        $item = DB::transaction(function () use ($request): Item {
            $item = Item::create([
                ...$request->safe()->except('attributes'),
                'item_code' => $this->itemCodeGenerator->generate(),
            ]);

            if ($request->has('attributes')) {
                $this->attributeValueService->sync(
                    $item,
                    $request->validated('attributes', [])
                );
            }

            return $item;
        });

        $item->load([
            'category',
            'itemType',
            'unitOfMeasure',
        ]);

        return response()->json([
            'data' => new ItemResource($item),
            'message' => 'Item created successfully.',
        ], 201);
    }

    public function show(Item $item): JsonResponse
    {
        $item->load([
            'category',
            'itemType',
            'unitOfMeasure',
        ]);

        return response()->json([
            'data' => new ItemResource($item),
        ]);
    }

    public function update(
        UpdateItemRequest $request,
        Item $item,
    ): JsonResponse {
        DB::transaction(function () use ($request, $item): void {
            $item->update(
                $request->safe()->except('attributes')
            );

            if ($request->has('attributes')) {
                $this->attributeValueService->sync(
                    $item->fresh(),
                    $request->validated('attributes', [])
                );
            }
        });

        $item->load([
            'category',
            'itemType',
            'unitOfMeasure',
        ]);

        return response()->json([
            'data' => new ItemResource($item->refresh()),
            'message' => 'Item updated successfully.',
        ]);
    }

    public function destroy(Item $item): JsonResponse
    {
        $item->delete();

        return response()->json([
            'message' => 'Item archived successfully.',
        ]);
    }

    public function restore(int $item): JsonResponse
    {
        $itemModel = Item::withTrashed()->findOrFail($item);

        $itemModel->restore();

        $itemModel->load([
            'category',
            'itemType',
            'unitOfMeasure',
        ]);

        return response()->json([
            'data' => new ItemResource($itemModel),
            'message' => 'Item restored successfully.',
        ]);
    }

    public function activate(Item $item): JsonResponse
    {
        $item->update([
            'is_active' => true,
        ]);

        return response()->json([
            'data' => new ItemResource(
                $item->refresh()->load([
                    'category',
                    'itemType',
                    'unitOfMeasure',
                ])
            ),
            'message' => 'Item activated successfully.',
        ]);
    }

    public function deactivate(Item $item): JsonResponse
    {
        $item->update([
            'is_active' => false,
        ]);

        return response()->json([
            'data' => new ItemResource(
                $item->refresh()->load([
                    'category',
                    'itemType',
                    'unitOfMeasure',
                ])
            ),
            'message' => 'Item deactivated successfully.',
        ]);
    }

    public function getAttributes(Item $item): JsonResponse
    {
        $item->load([
            'attributeValues.attributeDefinition',
        ]);

        $attributes = $item->attributeValues
            ->mapWithKeys(function ($attributeValue) {
                $definition = $attributeValue->attributeDefinition;

                $value = $attributeValue->value;

                if ($definition->data_type === 'multiselect') {
                    $value = json_decode(
                        $value,
                        true,
                        512,
                        JSON_THROW_ON_ERROR
                    );
                }

                if ($definition->data_type === 'integer') {
                    $value = (int) $value;
                }

                if ($definition->data_type === 'decimal') {
                    $value = (float) $value;
                }

                if ($definition->data_type === 'boolean') {
                    $value = $value === '1';
                }

                return [
                    $definition->code => $value,
                ];
            });

        return response()->json([
            'data' => $attributes,
        ]);
    }

    public function updateAttributes(
        Request $request,
        Item $item,
    ): JsonResponse {
        $validated = $request->validate([
            'attributes' => [
                'required',
                'array',
            ],
        ]);

        DB::transaction(function () use ($item, $validated): void {
            $this->attributeValueService->sync(
                $item,
                $validated['attributes']
            );
        });

        return response()->json([
            'data' => $this->getAttributeValues($item->refresh()),
            'message' => 'Item attributes updated successfully.',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function getAttributeValues(Item $item): array
    {
        $item->load([
            'attributeValues.attributeDefinition',
        ]);

        return $item->attributeValues
            ->mapWithKeys(function ($attributeValue) {
                $definition = $attributeValue->attributeDefinition;

                return [
                    $definition->code => $attributeValue->value,
                ];
            })
            ->all();
    }
}
