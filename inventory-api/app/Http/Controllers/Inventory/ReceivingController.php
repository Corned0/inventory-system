<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\Receiving\StoreReceivingRequest;
use App\Http\Requests\Receiving\UpdateReceivingRequest;
use App\Http\Resources\ReceivingResource;
use App\Models\Receiving;
use App\Services\Receiving\ReceivingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReceivingController extends Controller
{
    public function __construct(
        private readonly ReceivingService $receivingService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        $receivings = Receiving::query()
            ->with([
                'supplier',
                'warehouse',
                'receivedBy',
                'items.item',
            ])
            ->latest('received_date')
            ->paginate(20);

        return response()->json([
            'data' => ReceivingResource::collection(
                $receivings->items()
            ),
            'meta' => [
                'current_page' => $receivings->currentPage(),
                'per_page' => $receivings->perPage(),
                'total' => $receivings->total(),
                'last_page' => $receivings->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreReceivingRequest $request
    ): JsonResponse {
        $receiving = $this->receivingService->create(
            $request->validated()
        );

        return response()->json([
            'data' => new ReceivingResource($receiving),
            'message' => 'Receiving created successfully.',
        ], 201);
    }

    public function update(
        UpdateReceivingRequest $request,
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService->update(
            $receiving,
            $request->validated()
        );

        return $this->success(
            $receiving,
            'Receiving updated successfully.'
        );
    }

    public function show(
        Receiving $receiving
    ): JsonResponse {
        $receiving->load([
            'supplier',
            'warehouse',
            'receivedBy',
            'items.item',
            'items.lots.lot',
            'items.serials.serial',
        ]);

        return response()->json([
            'data' => new ReceivingResource($receiving),
        ]);
    }

    public function receive(
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService
            ->markReceived($receiving);

        return $this->success(
            $receiving,
            'Receiving marked as received.'
        );
    }

    public function inspect(
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService
            ->inspect($receiving);

        return $this->success(
            $receiving,
            'Receiving moved to inspection.'
        );
    }

    public function accept(
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService
            ->accept($receiving);

        return $this->success(
            $receiving,
            'Receiving accepted successfully.'
        );
    }

    public function reject(
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService
            ->reject($receiving);

        return $this->success(
            $receiving,
            'Receiving rejected successfully.'
        );
    }

    public function complete(
        Receiving $receiving
    ): JsonResponse {
        $receiving = $this->receivingService
            ->complete($receiving);

        return $this->success(
            $receiving,
            'Receiving completed successfully.'
        );
    }

    private function success(
        Receiving $receiving,
        string $message
    ): JsonResponse {
        return response()->json([
            'data' => new ReceivingResource($receiving),
            'message' => $message,
        ]);
    }
}
