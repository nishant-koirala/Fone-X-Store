<?php

namespace App\Models;

use App\Enums\DeviceUnitStatusEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceUnit extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_condition_id',
        'imei',
        'serial_number',
        'battery_health',
        'cost_price',
        'status',
        'order_item_id',
        'trade_in_valuation_id',
        'notes',
        'sold_at',
    ];

    protected $casts = [
        'status' => DeviceUnitStatusEnum::class,
        'cost_price' => 'decimal:2',
        'battery_health' => 'integer',
        'sold_at' => 'datetime',
    ];

    /**
     * @return BelongsTo<ProductCondition, $this>
     */
    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class);
    }

    /**
     * @return BelongsTo<OrderItem, $this>
     */
    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * @return BelongsTo<TradeInValuation, $this>
     */
    public function tradeInValuation(): BelongsTo
    {
        return $this->belongsTo(TradeInValuation::class);
    }

    /**
     * Scope for units ready for sale in inventory.
     *
     * @param  Builder<DeviceUnit>  $query
     * @return Builder<DeviceUnit>
     */
    public function scopeAvailable(Builder $query): Builder
    {
        return $query->where('status', DeviceUnitStatusEnum::AVAILABLE);
    }

    /**
     * Scope for sold units.
     *
     * @param  Builder<DeviceUnit>  $query
     * @return Builder<DeviceUnit>
     */
    public function scopeSold(Builder $query): Builder
    {
        return $query->where('status', DeviceUnitStatusEnum::SOLD);
    }

    /**
     * Mark this physical unit as sold to an order item.
     */
    public function markAsSold(OrderItem $orderItem): void
    {
        $this->update([
            'status' => DeviceUnitStatusEnum::SOLD,
            'order_item_id' => $orderItem->id,
            'sold_at' => now(),
        ]);
    }

    /**
     * Mark this physical unit as returned under customer guarantee.
     */
    public function markAsReturned(?string $reason = null): void
    {
        $note = $reason ? ($this->notes ? "{$this->notes}\nReturn Reason: {$reason}" : "Return Reason: {$reason}") : $this->notes;

        $this->update([
            'status' => DeviceUnitStatusEnum::RETURNED,
            'notes' => $note,
        ]);
    }
}
