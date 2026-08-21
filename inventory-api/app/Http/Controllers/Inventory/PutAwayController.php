<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\PutAway\StorePutAwayRequest;
use App\Http\Resources\PutAwayResource;
use App\Models\PutAway;
use App\Models\Receiving;
use App\Services\PutAway\PutAwayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PutAwayController extends Controller
{
    public function __construct(
        private readonly PutAwayService $putAwayService
    ) {
    }

    public function store(
        StorePutAwayRequest $request,
        Receiving $receiving
    ): JsonResponse {
        $putAway = $this->putAwayService->create(
            $receiving,
            $request->validated(),
            $request->user()->id
        );

        return response()->json([
            'data' => new PutAwayResource($putAway),
            'message' => 'Put-away created successfully.',
        ], 201);
    }

    public function show(
        PutAway $putAway
    ): JsonResponse {
        $putAway->load([
            'receiving',
            'warehouse',
            'performedBy',
            'items.item',
            'items.location',
            'items.lot',
            'items.serial',
            'items.receivingItem',
        ]);

        return response()->json([
            'data' => new PutAwayResource($putAway),
        ]);
    }

    public function complete(
        PutAway $putAway
    ): JsonResponse {
        $putAway = $this->putAwayService->complete(
            $putAway
        );

        return response()->json([
            'data' => new PutAwayResource($putAway),
            'message' => 'Put-away completed successfully.',
        ]);
    }
}
