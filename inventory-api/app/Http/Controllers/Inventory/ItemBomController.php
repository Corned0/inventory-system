<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\ItemBom\StoreItemBomComponentRequest;
use App\Http\Requests\ItemBom\StoreItemBomRequest;
use App\Http\Requests\ItemBom\UpdateItemBomComponentRequest;
use App\Http\Requests\ItemBom\UpdateItemBomRequest;
use App\Http\Resources\ItemBomComponentResource;
use App\Http\Resources\ItemBomResource;
use App\Models\Item;
use App\Models\ItemBom;
use App\Models\ItemBomComponent;
use App\Services\ItemBom\ItemBomService;
use Illuminate\Http\JsonResponse;

class ItemBomController extends Controller
{
    public function __construct(
        private readonly ItemBomService $service
    ) {
    }

    public function index(Item $item): JsonResponse
    {
        $boms = $item->boms()
            ->with([
                'item',
                'components.componentItem',
            ])
            ->orderByDesc('version')
            ->get();

        return response()->json([
            'data' => ItemBomResource::collection($boms),
        ]);
    }

    public function store(
        StoreItemBomRequest $request,
        Item $item
    ): JsonResponse {
        $bom = $this->service->create(
            $item,
            $request->validated()
        );

        $bom->load([
            'item',
            'components.componentItem',
        ]);

        return response()->json([
            'data' => new ItemBomResource($bom),
            'message' => 'BOM created successfully.',
        ], 201);
    }

    public function show(ItemBom $bom): JsonResponse
    {
        $bom->load([
            'item',
            'components.componentItem',
        ]);

        return response()->json([
            'data' => new ItemBomResource($bom),
        ]);
    }

    public function update(
        UpdateItemBomRequest $request,
        ItemBom $bom
    ): JsonResponse {
        $bom = $this->service->update(
            $bom,
            $request->validated()
        );

        $bom->load([
            'item',
            'components.componentItem',
        ]);

        return response()->json([
            'data' => new ItemBomResource($bom),
            'message' => 'BOM updated successfully.',
        ]);
    }

    public function storeComponent(
        StoreItemBomComponentRequest $request,
        ItemBom $bom
    ): JsonResponse {
        $component = $this->service->addComponent(
            $bom,
            $request->validated()
        );

        $component->load('componentItem');

        return response()->json([
            'data' => new ItemBomComponentResource($component),
            'message' => 'BOM component added successfully.',
        ], 201);
    }

    public function updateComponent(
        UpdateItemBomComponentRequest $request,
        ItemBom $bom,
        ItemBomComponent $component
    ): JsonResponse {
        abort_unless(
            $component->bom_id === $bom->id,
            404
        );

        $component = $this->service->updateComponent(
            $component,
            $request->validated()
        );

        $component->load('componentItem');

        return response()->json([
            'data' => new ItemBomComponentResource($component),
            'message' => 'BOM component updated successfully.',
        ]);
    }

    public function destroyComponent(
        ItemBom $bom,
        ItemBomComponent $component
    ): JsonResponse {
        abort_unless(
            $component->bom_id === $bom->id,
            404
        );

        $this->service->deleteComponent($component);

        return response()->json([
            'message' => 'BOM component deleted successfully.',
        ]);
    }
}
