<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_condition_id',
        'quantity',
        'price_at_purchase',
        'cost_price_at_purchase',
        'cost_total_at_purchase',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'price_at_purchase' => 'decimal:2',
        'cost_price_at_purchase' => 'decimal:2',
        'cost_total_at_purchase' => 'decimal:2',
    ];

    /**
     * Calculate line item gross profit if cost price is known.
     */
    public function getProfitAttribute(): ?float
    {
        $revenue = (float) ($this->price_at_purchase * $this->quantity);

        if ($this->cost_total_at_purchase !== null) {
            return $revenue - (float) $this->cost_total_at_purchase;
        }

        if ($this->cost_price_at_purchase !== null) {
            return (float) (($this->price_at_purchase - $this->cost_price_at_purchase) * $this->quantity);
        }

        return null;
    }

    /**
     * @return BelongsTo<Order, $this>
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * @return BelongsTo<ProductCondition, $this>
     */
    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class);
    }

    /**
     * @return HasMany<DeviceUnit, $this>
     */
    public function deviceUnits(): HasMany
    {
        return $this->hasMany(DeviceUnit::class);
    }

    /**
     * @return HasOne<DeviceUnit, $this>
     */
    public function deviceUnit(): HasOne
    {
        return $this->hasOne(DeviceUnit::class);
    }
}
