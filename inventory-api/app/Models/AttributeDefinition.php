<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class AttributeDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'data_type',
        'description',
        'is_required',
        'is_active',
        'sort_order',
        'validation_rules',
        'default_value',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'validation_rules' => 'array',
        ];
    }

    public function options(): HasMany
    {
        return $this->hasMany(AttributeOption::class)
            ->orderBy('sort_order');
    }

    public function itemTypes(): BelongsToMany
    {
        return $this->belongsToMany(
            ItemType::class,
            'item_type_attributes'
        )->withPivot([
            'is_required',
            'sort_order',
        ]);
    }
}
