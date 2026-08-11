<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
