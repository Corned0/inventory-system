<?php

namespace App\Models;

use App\Enums\AssetInstanceStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AssetInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'asset_number',
        'serial_number',
        'status',
        'current_warehouse_id',
        'current_location_id',
        'acquired_at',
        'acquisition_cost',
        'warranty_start',
        'warranty_end',
    ];

    protected function casts(): array
    {
        return [
            'status' => AssetInstanceStatus::class,
            'acquired_at' => 'datetime',
            'acquisition_cost' => 'decimal:4',
            'warranty_start' => 'date',
            'warranty_end' => 'date',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function currentWarehouse(): BelongsTo
    {
        return $this->belongsTo(
            Warehouse::class,
            'current_warehouse_id'
        );
    }

    public function currentLocation(): BelongsTo
    {
        return $this->belongsTo(
            Location::class,
            'current_location_id'
        );
    }
}
