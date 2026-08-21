<?php

namespace App\Services\PutAway;

use App\Enums\InventoryTransactionType;
use App\Enums\PutAwayStatus;
use App\Enums\ReceivingStatus;
use App\Exceptions\InvalidPutAwayStatusException;
use App\Exceptions\InvalidReceivingForPutAwayException;
use App\Models\InventoryTransaction;
use App\Models\PutAway;
use App\Models\PutAwayItem;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use App\Services\Inventory\InventoryTransactionNumberGenerator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PutAwayService
{
    public function __construct(
        private readonly InventoryTransactionNumberGenerator $numberGenerator,
    ) {}

    /**
     * Create a draft put-away for an eligible receiving.
     *
     * @param array{
     *     items: array<int, array{
     *         receiving_item_id: int,
     *         location_id: int,
     *         lot_id?: int|null,
     *         serial_id?: int|null,
     *         quantity: numeric-string|int|float
     *     }>
     * } $data
     */
    public function create(
        Receiving $receiving,
        array $data,
        int $performedBy
    ): PutAway {
        $this->ensureReceivingEligible($receiving);

        return DB::transaction(function () use (
            $receiving,
            $data,
            $performedBy
        ): PutAway {
            $receiving->load('items');

            $putAway = PutAway::query()->create([
                'put_away_number' => $this->generateNumber(),
                'receiving_id' => $receiving->id,
                'warehouse_id' => $receiving->warehouse_id,
                'status' => PutAwayStatus::Draft,
                'performed_by' => $performedBy,
            ]);

            foreach ($data['items'] as $itemData) {
                $receivingItem = $receiving->items
                    ->firstWhere(
                        'id',
                        $itemData['receiving_item_id']
                    );

                if (! $receivingItem instanceof ReceivingItem) {
                    throw new InvalidReceivingForPutAwayException();
                }

                $this->validateQuantity(
                    $receivingItem,
                    (float) $itemData['quantity']
                );

                $this->validateLocation(
                    $receiving->warehouse_id,
                    (int) $itemData['location_id']
                );

                PutAwayItem::query()->create([
                    'put_away_id' => $putAway->id,
                    'receiving_item_id' => $receivingItem->id,
                    'item_id' => $receivingItem->item_id,
                    'location_id' => $itemData['location_id'],
                    'lot_id' => $itemData['lot_id'] ?? null,
                    'serial_id' => $itemData['serial_id'] ?? null,
                    'quantity' => $itemData['quantity'],
                ]);
            }

            return $this->load($putAway);
        });
    }

    public function complete(
        PutAway $putAway
    ): PutAway {
        $this->ensureStatus(
            $putAway,
            PutAwayStatus::Draft
        );

        return DB::transaction(function () use ($putAway): PutAway {
            $putAway->load([
                'items.receivingItem',
                'items.item',
                'items.location',
                'items.lot',
                'items.serial',
            ]);

            foreach ($putAway->items as $putAwayItem) {
                $this->createInventoryTransaction(
                    $putAway,
                    $putAwayItem
                );
            }

            $putAway->update([
                'status' => PutAwayStatus::Completed,
            ]);

            return $this->load($putAway);
        });
    }

    private function createInventoryTransaction(
        PutAway $putAway,
        PutAwayItem $putAwayItem
    ): InventoryTransaction {
        $receivingItem = $putAwayItem->receivingItem;

        return InventoryTransaction::query()->create([
            'transaction_number' => $this->numberGenerator->generate(),

            'transaction_type' => InventoryTransactionType::PutAway,

            'item_id' => $putAwayItem->item_id,

            'warehouse_id' => $putAway->warehouse_id,

            'location_id' => $putAwayItem->location_id,

            'lot_id' => $putAwayItem->lot_id,

            'serial_id' => $putAwayItem->serial_id,

            'quantity' => $putAwayItem->quantity,

            'unit_cost' => $receivingItem->unit_cost,

            'reference_type' => PutAway::class,

            'reference_id' => $putAway->id,

            'transaction_date' => now(),

            'performed_by' => $putAway->performed_by,

            'remarks' => sprintf(
                'Put-away %s',
                $putAway->put_away_number
            ),
        ]);
    }

    private function ensureReceivingEligible(
        Receiving $receiving
    ): void {
        if (! in_array(
            $receiving->status,
            [
                ReceivingStatus::Accepted,
                ReceivingStatus::PartiallyAccepted,
            ],
            true
        )) {
            throw new InvalidReceivingForPutAwayException();
        }
    }

    private function ensureStatus(
        PutAway $putAway,
        PutAwayStatus $expected
    ): void {
        if ($putAway->status !== $expected) {
            throw new InvalidPutAwayStatusException(
                current: $putAway->status,
                expected: [$expected]
            );
        }
    }

    private function validateQuantity(
        ReceivingItem $receivingItem,
        float $quantity
    ): void {
        $alreadyPutAway = PutAwayItem::query()
            ->where('receiving_item_id', $receivingItem->id)
            ->whereHas(
                'putAway',
                fn ($query) => $query->where(
                    'status',
                    PutAwayStatus::Completed->value
                )
            )
            ->sum('quantity');

        $available = (float) $receivingItem->accepted_quantity
            - (float) $alreadyPutAway;

        if ($quantity > $available) {
            throw new \App\Exceptions\InsufficientStockException(
                available: (int) $available,
                requested: (int) $quantity,
            );
        }
    }

    private function validateLocation(
        int $warehouseId,
        int $locationId
    ): void {
        $belongsToWarehouse = DB::table('locations')
            ->where('id', $locationId)
            ->where('warehouse_id', $warehouseId)
            ->exists();

        if (! $belongsToWarehouse) {
            throw new InvalidReceivingForPutAwayException();
        }
    }

    private function generateNumber(): string
    {
        $number = DB::selectOne(
            "SELECT nextval('put_away_number_sequence') AS number"
        );

        return sprintf(
            'PA-%06d',
            $number->number
        );
    }

    private function load(
        PutAway $putAway
    ): PutAway {
        return $putAway->load([
            'receiving',
            'warehouse',
            'performedBy',
            'items.item',
            'items.location',
            'items.lot',
            'items.serial',
            'items.receivingItem',
        ]);
    }
}