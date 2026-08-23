<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\AssetInstance\StoreAssetInstanceRequest;
use App\Http\Requests\AssetInstance\UpdateAssetInstanceRequest;
use App\Http\Resources\AssetInstanceResource;
use App\Models\AssetInstance;
use App\Services\Inventory\AssetInstanceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AssetInstanceController extends Controller
{
    public function __construct(
        private readonly AssetInstanceService $assetInstanceService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $assetInstances = AssetInstance::query()
            ->with([
                'item',
                'currentWarehouse',
                'currentLocation',
            ])
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => AssetInstanceResource::collection(
                $assetInstances->items()
            ),
            'meta' => [
                'current_page' => $assetInstances->currentPage(),
                'per_page' => $assetInstances->perPage(),
                'total' => $assetInstances->total(),
                'last_page' => $assetInstances->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreAssetInstanceRequest $request
    ): JsonResponse {
        $assetInstance = $this->assetInstanceService->create(
            $request->validated()
        );

        return response()->json([
            'data' => new AssetInstanceResource($assetInstance),
            'message' => 'Asset instance created successfully.',
        ], 201);
    }

    public function show(
        AssetInstance $assetInstance
    ): JsonResponse {
        $assetInstance->load([
            'item',
            'currentWarehouse',
            'currentLocation',
        ]);

        return response()->json([
            'data' => new AssetInstanceResource($assetInstance),
        ]);
    }

    public function update(
        UpdateAssetInstanceRequest $request,
        AssetInstance $assetInstance
    ): JsonResponse {
        $assetInstance = $this->assetInstanceService->update(
            $assetInstance,
            $request->validated()
        );

        return response()->json([
            'data' => new AssetInstanceResource($assetInstance),
            'message' => 'Asset instance updated successfully.',
        ]);
    }
}
