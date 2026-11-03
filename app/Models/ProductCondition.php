<?php

namespace App\Models;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\GradeEnum;
use App\Enums\StockMovementTypeEnum;
use Illuminate\Database\Eloquent\Builder;
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
        'cost_price',
        'original_price',
        'quantity_in_stock',
    ];

    protected $casts = [
        'grade' => GradeEnum::class,
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
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
     * @return HasMany<DeviceUnit, $this>
     */
    public function deviceUnits(): HasMany
    {
        return $this->hasMany(DeviceUnit::class);
    }

    /**
     * @return HasMany<DeviceUnit, $this>
     */
    public function availableDeviceUnits(): HasMany
    {
        return $this->hasMany(DeviceUnit::class)->where('status', DeviceUnitStatusEnum::AVAILABLE);
    }

    /**
     * Scope a query to only include conditions that have positive stock.
     *
     * @param  Builder<ProductCondition>  $query
     * @return Builder<ProductCondition>
     */
    public function scopeInStock($query)
    {
        return $query->where('quantity_in_stock', '>', 0);
    }

    /**
     * Scope a query to only include conditions of visible products.
     *
     * @param  Builder<ProductCondition>  $query
     * @return Builder<ProductCondition>
     */
    public function scopeVisibleProduct($query)
    {
        return $query->whereHas('product', fn ($q) => $q->visible());
    }

    /**
     * Record a stock movement and adjust quantity_in_stock inside a DB transaction.
     *
     * @param  string|StockMovementTypeEnum  $type  ('restock', 'sale', 'exchange_in', 'adjustment')
     * @param  int  $quantity  (positive to increase stock, negative to decrease stock)
     */
    public function recordStockMovement(string|StockMovementTypeEnum $type, int $quantity, ?string $note = null, ?int $createdBy = null): StockMovement
    {
        $typeValue = $type instanceof StockMovementTypeEnum ? $type->value : $type;

        if ($quantity === 0) {
            throw new \InvalidArgumentException('Stock movement quantity cannot be zero.');
        }

        if ($typeValue === 'sale' && $quantity >= 0) {
            throw new \InvalidArgumentException("Sale stock movement must have a negative quantity. Given: {$quantity}.");
        }

        if (in_array($typeValue, ['restock', 'exchange_in']) && $quantity <= 0) {
            throw new \InvalidArgumentException("{$typeValue} stock movement must have a positive quantity. Given: {$quantity}.");
        }

        return DB::transaction(function () use ($type, $quantity, $note, $createdBy): StockMovement {
            /** @var ProductCondition $locked */
            $locked = static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($locked->quantity_in_stock + $quantity < 0) {
                throw new \InvalidArgumentException("Cannot reduce stock below zero for condition #{$this->id}. Available stock: {$locked->quantity_in_stock}, requested adjustment: {$quantity}.");
            }

            /** @var StockMovement $movement */
            $movement = $locked->stockMovements()->create([
                'type' => $type,
                'quantity' => $quantity,
                'note' => $note,
                'created_by' => $createdBy ?? auth()->id(),
            ]);

            $locked->increment('quantity_in_stock', $quantity);
            $this->quantity_in_stock = $locked->quantity_in_stock;

            return $movement;
        });
    }
}
