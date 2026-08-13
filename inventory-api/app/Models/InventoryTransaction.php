<?php

namespace App\Models;

use App\Enums\InventoryTransactionType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    protected $fillable = [
        'transaction_number',
        'transaction_type',
        'item_id',
        'warehouse_id',
        'location_id',
        'lot_id',
        'serial_id',
        'quantity',
        'unit_cost',
        'reference_type',
        'reference_id',
        'transaction_date',
        'performed_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'transaction_type' => InventoryTransactionType::class,
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'transaction_date' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
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

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }
}