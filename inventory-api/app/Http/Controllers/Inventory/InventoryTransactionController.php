<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryTransaction\IndexInventoryTransactionRequest;
use App\Http\Requests\InventoryTransaction\StoreInventoryTransactionRequest;
use App\Http\Resources\InventoryTransactionResource;
use App\Models\InventoryTransaction;
use App\Models\Item;
use App\Services\Inventory\InventoryBalanceService;
use App\Services\Inventory\InventoryTrackingService;
use App\Services\Inventory\InventoryTransactionNumberGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventoryTransactionController extends Controller
{
    public function __construct(
        private readonly InventoryBalanceService $balanceService,
        private readonly InventoryTransactionNumberGenerator $numberGenerator,
        private readonly InventoryTrackingService $trackingService,
    ) {}

    public function index(IndexInventoryTransactionRequest $request): JsonResponse
    {
        $filters = $request->validated();

        $transactions = InventoryTransaction::query()
            ->with([
                'item',
                'warehouse',
                'location',
                'lot',
                'serial',
                'performedBy',
            ])
            ->when(
                isset($filters['item_id']),
                fn ($query) => $query->where('item_id', $filters['item_id'])
            )
            ->when(
                isset($filters['warehouse_id']),
                fn ($query) => $query->where(
                    'warehouse_id',
                    $filters['warehouse_id']
                )
            )
            ->when(
                isset($filters['transaction_type']),
                fn ($query) => $query->where(
                    'transaction_type',
                    $filters['transaction_type']
                )
            )
            ->latest('transaction_date')
            ->paginate(20);

        return response()->json([
            'data' => InventoryTransactionResource::collection(
                $transactions->items()
            ),
            'meta' => [
                'current_page' => $transactions->currentPage(),
                'per_page' => $transactions->perPage(),
                'total' => $transactions->total(),
                'last_page' => $transactions->lastPage(),
            ],
        ]);
    }

    public function store(
        StoreInventoryTransactionRequest $request
    ): JsonResponse {
        $data = $request->validated();

        $transaction = DB::transaction(function () use ($data) {
            $item = Item::query()->with('itemType')->findOrFail($data['item_id']);

            $this->trackingService->validateForTransaction(
                item: $item,
                lotId: $data['lot_id'] ?? null,
                serialId: $data['serial_id'] ?? null,
                quantity: $data['quantity'] ?? null,
                warehouseId: $data['warehouse_id'] ?? null,
                locationId: $data['location_id'] ?? null,
            );

            $data['transaction_number'] =
                $this->numberGenerator->generate();

            $data['transaction_date'] ??= now();
            $data['performed_by'] = auth()->id();

            $transaction = InventoryTransaction::create($data);

            $this->balanceService->apply($transaction);

            return $transaction;
        });

        $transaction->load([
            'item',
            'warehouse',
            'location',
            'lot',
            'serial',
            'performedBy',
        ]);

        return response()->json([
            'data' => new InventoryTransactionResource(
                $transaction
            ),
            'message' => 'Inventory transaction created successfully.',
        ], 201);
    }

    public function show(
        InventoryTransaction $inventoryTransaction
    ): JsonResponse {
        return response()->json([
            'data' => new InventoryTransactionResource(
                $inventoryTransaction->load([
                    'item',
                    'warehouse',
                    'location',
                    'lot',
                    'serial',
                    'performedBy',
                ])
            ),
        ]);
    }
}
