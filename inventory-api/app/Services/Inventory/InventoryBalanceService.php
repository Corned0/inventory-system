<?php

namespace App\Services\Inventory;

use App\Enums\InventoryTransactionType;
use App\Exceptions\InsufficientStockException;
use App\Models\InventoryBalance;
use App\Models\InventoryTransaction;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryBalanceService
{
    /**
     * Apply an inventory transaction to the current balance.
     *
     * This method must be called whenever a transaction changes stock.
     */
    public function apply(
        InventoryTransaction $transaction
    ): InventoryBalance {
        return DB::transaction(function () use ($transaction) {
            $transaction->loadMissing([
                'item',
                'warehouse',
                'location',
                'lot',
            ]);

            $type = $transaction->transaction_type;

            if (!$type instanceof InventoryTransactionType) {
                $type = InventoryTransactionType::from(
                    $transaction->transaction_type
                );
            }

            return match ($type) {
                InventoryTransactionType::Receipt,
                InventoryTransactionType::PutAway,
                InventoryTransactionType::TransferIn,
                InventoryTransactionType::Return,
                InventoryTransactionType::AdjustmentIn,
                InventoryTransactionType::Assembly,
                InventoryTransactionType::Disassembly
                    => $this->increase($transaction),

                InventoryTransactionType::Issue,
                InventoryTransactionType::TransferOut,
                InventoryTransactionType::AdjustmentOut,
                InventoryTransactionType::Disposal
                    => $this->decrease($transaction),

                InventoryTransactionType::StockCount
                    => $this->applyStockCount($transaction),
            };
        });
    }

    /**
     * Increase stock.
     */
    private function increase(
        InventoryTransaction $transaction
    ): InventoryBalance {
        $balance = $this->getOrCreateBalance(
            $transaction
        );

        $quantity = $this->decimal(
            $transaction->quantity
        );

        $balance->quantity += $quantity;

        $this->recalculateAvailable($balance);

        $balance->save();

        return $balance->refresh();
    }

    /**
     * Decrease stock.
     */
    private function decrease(
        InventoryTransaction $transaction
    ): InventoryBalance {
        $balance = $this->getOrCreateBalance(
            $transaction
        );

        $quantity = $this->decimal(
            $transaction->quantity
        );

        $available = $this->decimal(
            $balance->available_quantity
        );

        if ($quantity > $available) {
            throw new InsufficientStockException(
                $quantity,
                $available
            );
        }

        $balance->quantity -= $quantity;

        $this->recalculateAvailable($balance);

        $balance->save();

        return $balance->refresh();
    }

    /**
     * Apply a physical stock count.
     *
     * For stock_count, transaction.quantity represents
     * the counted physical quantity, not a delta.
     */
    private function applyStockCount(
        InventoryTransaction $transaction
    ): InventoryBalance {
        $balance = $this->getOrCreateBalance(
            $transaction
        );

        $countedQuantity = $this->decimal(
            $transaction->quantity
        );

        $reservedQuantity = $this->decimal(
            $balance->reserved_quantity
        );

        if ($countedQuantity < $reservedQuantity) {
            throw new InvalidArgumentException(
                'Stock count cannot be lower than the reserved quantity.'
            );
        }

        $balance->quantity = $countedQuantity;

        $this->recalculateAvailable($balance);

        $balance->save();

        return $balance->refresh();
    }

    /**
     * Find an existing balance row using the natural inventory key.
     *
     * NULL location_id and NULL lot_id are intentionally part
     * of the unique inventory balance key.
     */
    private function getOrCreateBalance(
        InventoryTransaction $transaction
    ): InventoryBalance {
        $attributes = [
            'item_id' => $transaction->item_id,
            'warehouse_id' => $transaction->warehouse_id,
            'location_id' => $transaction->location_id,
            'lot_id' => $transaction->lot_id,
        ];

        $balance = InventoryBalance::query()
            ->where($attributes)
            ->lockForUpdate()
            ->first();

        if ($balance !== null) {
            return $balance;
        }

        InventoryBalance::query()->insertOrIgnore([
            ...$attributes,
            'quantity' => 0,
            'reserved_quantity' => 0,
            'available_quantity' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return InventoryBalance::query()
            ->where($attributes)
            ->lockForUpdate()
            ->firstOrFail();
    }

    /**
     * Keep available quantity derived from quantity and reservations.
     */
    private function recalculateAvailable(
        InventoryBalance $balance
    ): void {
        $balance->available_quantity = max(
            0,
            $this->decimal($balance->quantity)
            - $this->decimal($balance->reserved_quantity)
        );
    }

    /**
     * Apply nullable equality correctly.
     */
    private function whereNullable(
        Builder $query,
        string $column,
        int|string|null $value
    ): void {
        if ($value === null) {
            $query->whereNull($column);

            return;
        }

        $query->where($column, $value);
    }

    /**
     * Normalize decimal database values.
     */
    private function decimal(
        int|float|string|null $value
    ): float {
        return (float) ($value ?? 0);
    }
}