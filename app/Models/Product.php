<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'brand',
        'image',
        'description',
        'category_id',
        'base_price',
        'is_new',
        'is_visible',
        'is_featured',
        'gallery',
    ];

    protected $casts = [
        'base_price' => 'decimal:2',
        'is_new' => 'boolean',
        'is_visible' => 'boolean',
        'is_featured' => 'boolean',
        'gallery' => 'array',
    ];

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductCondition, $this>
     */
    public function conditions(): HasMany
    {
        return $this->hasMany(ProductCondition::class);
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }
}
