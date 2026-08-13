<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InventoryTransaction extends Model
{
    use HasFactory;

    public const UPDATED_AT = null;

    public const TYPES = [
        'receipt',
        'put_away',
        'issue',
        'transfer_out',
        'transfer_in',
        'return',
        'adjustment_in',
        'adjustment_out',
        'disposal',
        'assembly',
        'disassembly',
        'stock_count',
    ];

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
        return $this->belongsTo(InventoryLot::class);
    }

    public function serial(): BelongsTo
    {
        return $this->belongsTo(InventorySerial::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'performed_by');
    }
}
