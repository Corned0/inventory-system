<?php

namespace App\Services\Receiving;

use App\Enums\ReceivingStatus;
use App\Exceptions\BusinessException;
use App\Exceptions\InvalidReceivingStatusException;
use App\Exceptions\InvalidReceivingStatusExceptionArrayValue;
use App\Models\Receiving;
use App\Models\ReceivingItem;
use Illuminate\Support\Facades\DB;

class ReceivingService
{
    public function create(array $data): Receiving
    {
        return DB::transaction(function () use ($data) {
            $receiving = Receiving::create([
                'receiving_number' => app(
                    ReceivingNumberGenerator::class
                )->generate(),

                'supplier_id' => $data['supplier_id'],
                'purchase_order_id' => $data['purchase_order_id'] ?? null,
                'warehouse_id' => $data['warehouse_id'],
                'received_date' => $data['received_date'] ?? now(),
                'status' => ReceivingStatus::Draft,
                'received_by' => auth()->id(),
                'remarks' => $data['remarks'] ?? null,
            ]);

            foreach ($data['items'] as $item) {
                $this->createItem(
                    $receiving,
                    $item
                );
            }

            return $this->load($receiving);
        });
    }

    private function createItem(
        Receiving $receiving,
        array $data
    ): ReceivingItem {
        return $receiving->items()->create([
            'item_id' => $data['item_id'],
            'ordered_quantity' => $data['ordered_quantity'] ?? 0,
            'received_quantity' => $data['received_quantity'],
            'accepted_quantity' => $data['accepted_quantity'] ?? 0,
            'rejected_quantity' => $data['rejected_quantity'] ?? 0,
            'unit_cost' => $data['unit_cost'] ?? null,
        ]);
    }

    public function markReceived(
        Receiving $receiving
    ): Receiving {
        $this->ensureStatus(
            $receiving,
            ReceivingStatus::Draft
        );

        $receiving->update([
            'status' => ReceivingStatus::Received,
        ]);

        return $this->load($receiving);
    }

    public function inspect(
        Receiving $receiving
    ): Receiving {
        $this->ensureStatus(
            $receiving,
            ReceivingStatus::Received
        );

        $receiving->update([
            'status' => ReceivingStatus::UnderInspection,
        ]);

        return $this->load($receiving);
    }

    public function accept(
        Receiving $receiving
    ): Receiving {
        $this->ensureStatus(
            $receiving,
            ReceivingStatus::UnderInspection
        );

        DB::transaction(function () use ($receiving) {
            $receiving->items()->update([
                'accepted_quantity' => DB::raw(
                    'received_quantity'
                ),
                'rejected_quantity' => 0,
            ]);

            $receiving->update([
                'status' => ReceivingStatus::Accepted,
            ]);
        });

        return $this->load($receiving);
    }

    public function reject(
        Receiving $receiving
    ): Receiving {
        $this->ensureStatus(
            $receiving,
            ReceivingStatus::UnderInspection
        );

        DB::transaction(function () use ($receiving) {
            $receiving->items()->update([
                'accepted_quantity' => 0,
                'rejected_quantity' => DB::raw(
                    'received_quantity'
                ),
            ]);

            $receiving->update([
                'status' => ReceivingStatus::Rejected,
            ]);
        });

        return $this->load($receiving);
    }

    public function complete(
        Receiving $receiving
    ): Receiving {
        $expected = [
            ReceivingStatus::Accepted,
            ReceivingStatus::PartiallyAccepted,
        ];

        if (! in_array($receiving->status, $expected, true)) {
            throw new InvalidReceivingStatusExceptionArrayValue(
                current: $receiving->status,
                expected: $expected,
            );
        }

        $receiving->update([
            'status' => ReceivingStatus::Completed,
        ]);

        return $this->load($receiving);
    }

    private function ensureStatus(
        Receiving $receiving,
        ReceivingStatus $expected
    ): void {
        if ($receiving->status !== $expected) {
            throw new InvalidReceivingStatusException(
                current: $receiving->status,
                expected: $expected,
            );
        }
    }

    private function load(
        Receiving $receiving
    ): Receiving {
        return $receiving->load([
            'supplier',
            'warehouse',
            'items.item',
            'items.lots.lot',
            'items.serials.serial',
        ]);
    }
}