<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemTypeAttribute extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_type_id',
        'attribute_definition_id',
        'is_required',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'item_type_id' => 'integer',
            'attribute_definition_id' => 'integer',
            'is_required' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function itemType(): BelongsTo
    {
        return $this->belongsTo(ItemType::class);
    }

    public function attributeDefinition(): BelongsTo
    {
        return $this->belongsTo(AttributeDefinition::class,'attribute_definition_id');
    }
}
