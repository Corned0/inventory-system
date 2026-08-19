<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReceivingItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_id',
        'item_id',
        'ordered_quantity',
        'received_quantity',
        'accepted_quantity',
        'rejected_quantity',
        'unit_cost',
    ];

    protected function casts(): array
    {
        return [
            'ordered_quantity' => 'decimal:4',
            'received_quantity' => 'decimal:4',
            'accepted_quantity' => 'decimal:4',
            'rejected_quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
        ];
    }

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function lots(): HasMany
    {
        return $this->hasMany(ReceivingItemLot::class);
    }

    public function serials(): HasMany
    {
        return $this->hasMany(ReceivingItemSerial::class);
    }
}
