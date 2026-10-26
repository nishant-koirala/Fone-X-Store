<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\DB;

class ProductCondition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'grade',
        'price',
        'original_price',
        'quantity_in_stock',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'quantity_in_stock' => 'integer',
    ];

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * @return HasMany<StockMovement, $this>
     */
    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Record a stock movement and adjust quantity_in_stock inside a DB transaction.
     *
     * @param  string  $type  ('restock', 'sale', 'exchange_in', 'adjustment')
     * @param  int  $quantity  (positive to increase stock, negative to decrease stock)
     */
    public function recordStockMovement(string $type, int $quantity, ?string $note = null, ?int $createdBy = null): StockMovement
    {
        if ($quantity === 0) {
            throw new \InvalidArgumentException('Stock movement quantity cannot be zero.');
        }

        if ($type === 'sale' && $quantity >= 0) {
            throw new \InvalidArgumentException("Sale stock movement must have a negative quantity. Given: {$quantity}.");
        }

        if (in_array($type, ['restock', 'exchange_in']) && $quantity <= 0) {
            throw new \InvalidArgumentException("{$type} stock movement must have a positive quantity. Given: {$quantity}.");
        }

        if ($this->quantity_in_stock + $quantity < 0) {
            throw new \InvalidArgumentException("Cannot reduce stock below zero for condition #{$this->id}. Available stock: {$this->quantity_in_stock}, requested adjustment: {$quantity}.");
        }

        return DB::transaction(function () use ($type, $quantity, $note, $createdBy): StockMovement {
            /** @var StockMovement $movement */
            $movement = $this->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'note' => $note,
                'created_by' => $createdBy ?? auth()->id(),
            ]);

            $this->increment('quantity_in_stock', $quantity);

            return $movement;
        });
    }
}
