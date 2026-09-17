<?php

namespace App\Services\Inventory;

use App\Enums\InventorySerialStatus;
use App\Models\InventoryLot;
use App\Models\InventorySerial;
use App\Models\Item;
use App\Models\Location;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryTrackingService
{
    public function validateForTransaction(
        Item $item,
        ?int $lotId = null,
        ?int $serialId = null,
        int|float|string|null $quantity = null,
        ?int $warehouseId = null,
        ?int $locationId = null,
    ): void {
        $trackingType = $item->itemType?->tracking_type ?? 'none';

        if ($trackingType === 'none') {
            if ($lotId !== null) {
                throw new InvalidArgumentException('Lot tracking is not allowed for this item.');
            }

            if ($serialId !== null) {
                throw new InvalidArgumentException('Serial tracking is not allowed for this item.');
            }

            return;
        }

        if ($trackingType === 'lot') {
            if ($lotId === null) {
                throw new InvalidArgumentException('A lot_id is required for lot-tracked items.');
            }

            if ($serialId !== null) {
                throw new InvalidArgumentException('Serial tracking is not allowed for lot-tracked items.');
            }

            $lot = InventoryLot::query()->find($lotId);

            if ($lot === null || (int) $lot->item_id !== (int) $item->id) {
                throw new InvalidArgumentException('The provided lot does not belong to the item.');
            }

            return;
        }

        if ($trackingType === 'serial') {
            if ($serialId === null) {
                throw new InvalidArgumentException('A serial_id is required for serial-tracked items.');
            }

            if ($lotId !== null) {
                throw new InvalidArgumentException('Lot tracking is not allowed for serial-tracked items.');
            }

            $serial = InventorySerial::query()->find($serialId);

            if ($serial === null || (int) $serial->item_id !== (int) $item->id) {
                throw new InvalidArgumentException('The provided serial does not belong to the item.');
            }

            $normalizedQuantity = (float) ($quantity ?? 0);

            if ($normalizedQuantity !== 1.0) {
                throw new InvalidArgumentException('Serialized inventory transactions must have a quantity of 1.');
            }
        }

        if ($warehouseId !== null && $locationId !== null) {
            $belongsToWarehouse = DB::table('locations')
                ->where('id', $locationId)
                ->where('warehouse_id', $warehouseId)
                ->exists();

            if (! $belongsToWarehouse) {
                throw new InvalidArgumentException('The selected location does not belong to the warehouse.');
            }
        }
    }

    public function validateLocationWarehouse(
        ?int $warehouseId,
        ?int $locationId
    ): void {
        if ($warehouseId === null || $locationId === null) {
            return;
        }

        $belongsToWarehouse = DB::table('locations')
            ->where('id', $locationId)
            ->where('warehouse_id', $warehouseId)
            ->exists();

        if (! $belongsToWarehouse) {
            throw new InvalidArgumentException('The selected location does not belong to the warehouse.');
        }
    }

    public function ensureSerialCanIssue(InventorySerial $serial): void
    {
        if ($serial->status === InventorySerialStatus::Issued || $serial->status === InventorySerialStatus::Disposed) {
            throw new InvalidArgumentException('This serial is not available for issue.');
        }
    }
}
