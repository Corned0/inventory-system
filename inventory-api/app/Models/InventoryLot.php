<?php

namespace App\Models;

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
