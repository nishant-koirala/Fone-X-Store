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
use Illuminate\Validation\ValidationException;

class ProductCondition extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'product_id',
        'grade',
        'price',
        'cost_price',
        'tracks_units',
        'original_price',
        'quantity_in_stock',
    ];

    protected $casts = [
        'grade' => GradeEnum::class,
        'price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'tracks_units' => 'boolean',
        'original_price' => 'decimal:2',
        'quantity_in_stock' => 'integer',
    ];

    /**
     * @var list<string>
     */
    protected $appends = [
        'battery_range',
        'battery_claim',
        'has_imei_registered',
        'imei_claim',
    ];

    protected static function booted(): void
    {
        static::saving(function (ProductCondition $condition) {
            if ($condition->exists && $condition->isDirty('tracks_units') && $condition->tracks_units && ! $condition->getOriginal('tracks_units')) {
                $availableCount = $condition->availableDeviceUnits()->count();
                if ($availableCount !== $condition->quantity_in_stock) {
                    throw ValidationException::withMessages([
                        'tracks_units' => "Cannot enable unit tracking: this condition has {$condition->quantity_in_stock} unit(s) in stock but {$availableCount} available device unit(s) registered. Please register device units until the numbers match.",
                    ]);
                }
            }
        });
    }

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

    /**
     * Get the battery health range across available units if tracked.
     */
    public function getBatteryRangeAttribute(): ?string
    {
        if (! $this->tracks_units) {
            return null;
        }

        $units = $this->relationLoaded('availableDeviceUnits')
            ? $this->availableDeviceUnits
            : $this->availableDeviceUnits()->get();

        $batteries = $units->pluck('battery_health')->filter(fn ($b) => $b !== null);

        if ($batteries->isEmpty()) {
            return null;
        }

        $min = $batteries->min();
        $max = $batteries->max();

        return $min === $max ? "{$min}%" : "{$min}% - {$max}%";
    }

    /**
     * Get battery claim display text.
     */
    public function getBatteryClaimAttribute(): ?string
    {
        $range = $this->battery_range;

        return $range !== null ? "Battery: {$range}" : null;
    }

    /**
     * Determine if every available unit has a registered IMEI.
     */
    public function getHasImeiRegisteredAttribute(): bool
    {
        if (! $this->tracks_units) {
            return false;
        }

        $units = $this->relationLoaded('availableDeviceUnits')
            ? $this->availableDeviceUnits
            : $this->availableDeviceUnits()->get();

        if ($units->isEmpty()) {
            return false;
        }

        return $units->every(fn (DeviceUnit $u) => ! empty($u->imei));
    }

    /**
     * Get IMEI claim display text.
     */
    public function getImeiClaimAttribute(): ?string
    {
        return $this->has_imei_registered ? 'IMEI registered' : null;
    }
}
