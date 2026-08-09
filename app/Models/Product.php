<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'brand',
        'image',
        'description',
        'category_id',
        'base_price',
        'is_new',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_new' => 'boolean',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function conditions(): HasMany
    {
        return $this->hasMany(ProductCondition::class);
    }
}
