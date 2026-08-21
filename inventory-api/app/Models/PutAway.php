<?php

namespace App\Models;

use App\Enums\PutAwayStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PutAway extends Model
{
    use HasFactory;

    protected $fillable = [
        'put_away_number',
        'receiving_id',
        'warehouse_id',
        'status',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PutAwayStatus::class,
        ];
    }

    public function receiving(): BelongsTo
    {
        return $this->belongsTo(Receiving::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function performedBy(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'performed_by'
        );
    }

    public function items(): HasMany
    {
        return $this->hasMany(PutAwayItem::class);
    }
}
