<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class ProductCondition extends Model
{
    use HasFactory;

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

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Record a stock movement and adjust quantity_in_stock inside a DB transaction.
     *
     * @param string $type ('restock', 'sale', 'exchange_in', 'adjustment')
     * @param int $quantity (positive to increase stock, negative to decrease stock)
     * @param string|null $note
     * @param int|null $createdBy
     * @return StockMovement
     */
    public function recordStockMovement(string $type, int $quantity, ?string $note = null, ?int $createdBy = null): StockMovement
    {
        return DB::transaction(function () use ($type, $quantity, $note, $createdBy) {
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
