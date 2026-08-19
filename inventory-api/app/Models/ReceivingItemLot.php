<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingItemLot extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_item_id',
        'lot_id',
        'quantity',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
        ];
    }

    public function receivingItem(): BelongsTo
    {
        return $this->belongsTo(ReceivingItem::class);
    }

    public function lot(): BelongsTo
    {
        return $this->belongsTo(InventoryLot::class);
    }
}
