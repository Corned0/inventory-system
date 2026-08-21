<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PutAwayItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'put_away_id',
        'receiving_item_id',
        'item_id',
        'location_id',
        'lot_id',
        'serial_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function putAway(): BelongsTo
    {
        return $this->belongsTo(PutAway::class);
    }

    public function receivingItem(): BelongsTo
    {
        return $this->belongsTo(
            ReceivingItem::class
        );
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(
            InventoryLot::class,
            'lot_id'
        );
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(
            InventorySerial::class,
            'serial_id'
        );
    }
}
