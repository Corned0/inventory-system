<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class ItemType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'tracking_type',
        'is_asset',
        'is_composite',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_asset' => 'boolean',
            'is_composite' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function attributes(): HasMany
    {
        return $this->hasMany(ItemTypeAttribute::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class);
    }

    public function attributeDefinitions(): BelongsToMany
    {
        return $this->belongsToMany(
            AttributeDefinition::class,
            'item_type_attributes'
        )->withPivot([
            'is_required',
            'sort_order',
        ]);
    }
}
