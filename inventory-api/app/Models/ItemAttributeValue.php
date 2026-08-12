<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemAttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'item_id',
        'attribute_definition_id',
        'value',
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }

    public function attributeDefinition(): BelongsTo
    {
        return $this->belongsTo(
            AttributeDefinition::class
        );
    }
}
