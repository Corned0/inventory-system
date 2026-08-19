<?php

namespace App\Models;

use App\Enums\ReceivingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Receiving extends Model
{
    use HasFactory;

    protected $fillable = [
        'receiving_number',
        'supplier_id',
        'purchase_order_id',
        'warehouse_id',
        'received_date',
        'status',
        'received_by',
        'remarks',
    ];

    protected function casts(): array
    {
        return [
            'received_date' => 'datetime',
            'status' => ReceivingStatus::class,
        ];
    }

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /* public function purchaseOrder(): BelongsTo
    {
        return $this->belongsTo(PurchaseOrder::class);
    } */

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ReceivingItem::class);
    }
}
