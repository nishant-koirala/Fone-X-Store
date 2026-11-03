<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Services\Inventory\UnitInventoryService;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id',
        'user_id',
        'status',
        'delivery_zone',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
        'confirmed_at',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'total' => 'decimal:2',
        'confirmed_at' => 'datetime',
        'restocked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::updating(function (Order $order) {
            $originalStatus = $order->getOriginal('status');
            $wasCancelled = $originalStatus instanceof OrderStatusEnum
                ? $originalStatus === OrderStatusEnum::CANCELLED
                : $originalStatus === OrderStatusEnum::CANCELLED->value;

            if ($wasCancelled && $order->status !== OrderStatusEnum::CANCELLED) {
                throw new \LogicException('A cancelled order cannot be changed to another status. Staff must create a new order instead.');
            }
        });

        static::updated(function (Order $order) {
            $isCancelled = $order->status === OrderStatusEnum::CANCELLED;
            if ($order->wasChanged('status') && $isCancelled && $order->restocked_at === null) {
                DB::transaction(function () use ($order) {
                    $order->loadMissing('items.productCondition');
                    foreach ($order->items as $item) {
                        if ($item->productCondition) {
                            $item->productCondition->recordStockMovement(
                                type: StockMovementTypeEnum::RESTOCK,
                                quantity: $item->quantity,
                                note: "Automatic restock from cancelled Order #{$order->id}",
                                createdBy: auth()->id()
                            );
                        }
                    }

                    app(UnitInventoryService::class)->restockOrderUnits($order, auth()->id());

                    $order->forceFill(['restocked_at' => now()])->saveQuietly();
                });
            }
        });

        // Guard: if a non-cancelled order with unreturned stock is deleted (soft or force),
        // restock its items first so stock numbers stay accurate.
        static::deleting(function (Order $order) {
            $needsRestock = $order->status !== OrderStatusEnum::CANCELLED
                && $order->restocked_at === null;

            if ($needsRestock) {
                DB::transaction(function () use ($order) {
                    $order->loadMissing('items.productCondition');
                    foreach ($order->items as $item) {
                        if ($item->productCondition) {
                            $item->productCondition->recordStockMovement(
                                type: StockMovementTypeEnum::RESTOCK,
                                quantity: $item->quantity,
                                note: "Restock from deleted Order #{$order->id}",
                                createdBy: auth()->id()
                            );
                        }
                    }

                    app(UnitInventoryService::class)->restockOrderUnits($order, auth()->id());

                    $order->forceFill(['restocked_at' => now()])->saveQuietly();
                });
            }
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Customer, $this>
     */
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Calculate total gross profit for this order if cost prices are known.
     * Returns null if no line items have a cost_price_at_purchase recorded.
     */
    public function calculateProfit(): ?float
    {
        $this->loadMissing(['items.deviceUnits']);

        if ($this->items->isEmpty()) {
            return 0.0;
        }

        $totalProfit = 0.0;
        $hasAnyCost = false;

        foreach ($this->items as $item) {
            if ($item->cost_price_at_purchase !== null) {
                $revenue = (float) ($item->price_at_purchase * $item->quantity);
                $totalCost = $item->deviceUnits->isNotEmpty()
                    ? (float) $item->cost_price_at_purchase
                    : (float) ($item->cost_price_at_purchase * $item->quantity);

                $totalProfit += ($revenue - $totalCost);
                $hasAnyCost = true;
            }
        }

        return $hasAnyCost ? $totalProfit : null;
    }
}
