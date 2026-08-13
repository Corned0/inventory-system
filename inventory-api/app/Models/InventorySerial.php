<?php

namespace App\Models;

use App\Enums\InventorySerialStatus;
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
    ];

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
}
