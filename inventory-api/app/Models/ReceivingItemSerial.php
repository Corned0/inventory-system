<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceivingItemSerial extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_item_id',
        'serial_id',
    ];

    public function receivingItem(): BelongsTo
    {
        return $this->belongsTo(ReceivingItem::class);
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(
            InventorySerial::class,
            'serial_id'
        );
    }
}
