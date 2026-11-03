<?php

namespace App\Models;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Enums\TradeInStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;

class TradeInValuation extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'product_condition_id',
        'user_id',
        'device_brand',
        'device_model',
        'condition_description',
        'estimated_value',
        'status',
        'stock_ingested_at',
    ];

    protected $casts = [
        'status' => TradeInStatusEnum::class,
        'estimated_value' => 'decimal:2',
        'stock_ingested_at' => 'datetime',
    ];

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
     * @return BelongsTo<ProductCondition, $this>
     */
    public function productCondition(): BelongsTo
    {
        return $this->belongsTo(ProductCondition::class);
    }

    /**
     * @return HasOne<DeviceUnit, $this>
     */
    public function deviceUnit(): HasOne
    {
        return $this->hasOne(DeviceUnit::class);
    }

    /**
     * Ingest this accepted trade-in phone into inventory (type: exchange_in).
     * Idempotent: cannot double-ingest stock if called multiple times.
     */
    public function ingestIntoStock(ProductCondition $condition, ?int $staffUserId = null, ?string $imei = null, ?int $batteryHealth = null): ?StockMovement
    {
        return DB::transaction(function () use ($condition, $staffUserId, $imei, $batteryHealth): ?StockMovement {
            /** @var TradeInValuation $locked */
            $locked = static::query()->whereKey($this->id)->lockForUpdate()->firstOrFail();

            if ($locked->stock_ingested_at !== null) {
                return null;
            }

            $customerName = $locked->customer->name ?? 'Customer';
            $note = "Trade-in #{$locked->id} accepted: {$locked->device_brand} {$locked->device_model} from {$customerName}";

            $movement = $condition->recordStockMovement(
                type: StockMovementTypeEnum::EXCHANGE_IN,
                quantity: 1,
                note: $note,
                createdBy: $staffUserId ?? auth()->id()
            );

            // If condition has no cost price set, apply estimated trade-in valuation as cost basis
            if ($locked->estimated_value !== null && $condition->cost_price === null) {
                $condition->update(['cost_price' => $locked->estimated_value]);
            }

            // Create individual serialized device unit
            DeviceUnit::create([
                'product_condition_id' => $condition->id,
                'imei' => $imei,
                'battery_health' => $batteryHealth,
                'cost_price' => $locked->estimated_value,
                'status' => DeviceUnitStatusEnum::AVAILABLE,
                'trade_in_valuation_id' => $locked->id,
                'notes' => "Trade-in from {$customerName} ({$locked->device_brand} {$locked->device_model})",
            ]);

            $locked->forceFill([
                'product_condition_id' => $condition->id,
                'status' => TradeInStatusEnum::COMPLETED,
                'stock_ingested_at' => now(),
            ])->saveQuietly();

            $this->stock_ingested_at = $locked->stock_ingested_at;
            $this->status = $locked->status;
            $this->product_condition_id = $locked->product_condition_id;

            return $movement;
        });
    }
}
