<?php

namespace App\Models;

use App\Exceptions\InventoryLotDateRangeException;
use App\Exceptions\InventoryLotDeleteException;
use App\Exceptions\InventoryLotItemChangeException;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    /** @use HasFactory<\Database\Factories\InventoryLotFactory> */
    use HasFactory;

    protected $fillable = [
        'item_id',
        'lot_number',
        'manufactured_date',
        'expiration_date',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $lot): void {
            if ($lot->isDirty('item_id')) {
                $hasReferences = $lot->transactions()->exists() || $lot->balances()->exists();

                if ($hasReferences) {
                    throw new InventoryLotItemChangeException();
                }
            }

            if (
                $lot->manufactured_date !== null
                && $lot->expiration_date !== null
                && $lot->expiration_date->lt($lot->manufactured_date)
            ) {
                throw new InventoryLotDateRangeException();
            }
        });

        static::deleting(function (self $lot): void {
            if ($lot->transactions()->exists() || $lot->balances()->exists()) {
                throw new InventoryLotDeleteException();
            }
        });
    }

    public function transactions()
    {
        return $this->hasMany(InventoryTransaction::class, 'lot_id');
    }

    public function balances()
    {
        return $this->hasMany(InventoryBalance::class, 'lot_id');
    }

    protected function casts(): array
    {
        return [
            'manufactured_date' => 'date',
            'expiration_date' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
