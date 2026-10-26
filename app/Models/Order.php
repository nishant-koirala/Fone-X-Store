<?php

namespace App\Models;

use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'user_id',
        'status',
        'total',
        'shipping_name',
        'shipping_phone',
        'shipping_email',
        'shipping_address',
    ];

    protected $casts = [
        'status' => OrderStatusEnum::class,
        'total' => 'decimal:2',
        'restocked_at' => 'datetime',
    ];

    protected static function booted(): void
    {
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
}
