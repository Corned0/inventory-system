<?php

namespace App\Models;

use App\Enums\InventorySerialStatus;
use App\Exceptions\InventorySerialItemChangeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventorySerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'serial_number',
        'status',
        'current_warehouse_id',
        'current_location_id',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $serial): void {
            if ($serial->isDirty('item_id')) {
                $hasReferences = $serial->transactions()->exists() || $serial->assetInstances()->exists();

                if ($hasReferences) {
                    throw new InventorySerialItemChangeException();
                }
            }
        });

        static::deleting(function (self $serial): void {
            if ($serial->transactions()->exists() || $serial->assetInstances()->exists()) {
                throw new \RuntimeException('The serial cannot be deleted because it is referenced by inventory records.');
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => InventorySerialStatus::class,
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'current_warehouse_id');
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_location_id');
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'serial_id');
    }

    public function assetInstances()
    {
        return $this->hasMany(AssetInstance::class, 'serial_number', 'serial_number');
    }
}
