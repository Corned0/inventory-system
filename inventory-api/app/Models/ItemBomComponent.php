<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemBomComponent extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_id',
        'component_item_id',
        'quantity',
        'is_required',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:4',
            'is_required' => 'boolean',
        ];
    }

    public function bom(): BelongsTo
    {
        return $this->belongsTo(
            ItemBom::class,
            'bom_id'
        );
    }

    public function componentItem(): BelongsTo
    {
        return $this->belongsTo(
            Item::class,
            'component_item_id'
        );
    }
}
