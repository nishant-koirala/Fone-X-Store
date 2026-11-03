<?php

namespace App\Models;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Enums\TradeInStatusEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
     * @return \Illuminate\Database\Eloquent\Relations\HasOne<DeviceUnit, $this>
     */
    public function deviceUnit(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DeviceUnit::class);
    }

    /**
     * Ingest this accepted trade-in phone into inventory (type: exchange_in).
     * Idempotent: cannot double-ingest stock if called multiple times.
     */
    public function ingestIntoStock(ProductCondition $condition, ?int $staffUserId = null, ?string $imei = null, ?int $batteryHealth = null): ?StockMovement
    {
        if ($this->stock_ingested_at !== null) {
            return null;
        }

        return DB::transaction(function () use ($condition, $staffUserId, $imei, $batteryHealth): StockMovement {
            $customerName = $this->customer->name ?? 'Customer';
            $note = "Trade-in #{$this->id} accepted: {$this->device_brand} {$this->device_model} from {$customerName}";

            $movement = $condition->recordStockMovement(
                type: StockMovementTypeEnum::EXCHANGE_IN,
                quantity: 1,
                note: $note,
                createdBy: $staffUserId ?? auth()->id()
            );

            // If condition has no cost price set, apply estimated trade-in valuation as cost basis
            if ($this->estimated_value !== null && $condition->cost_price === null) {
                $condition->update(['cost_price' => $this->estimated_value]);
            }

            // Create individual serialized device unit
            DeviceUnit::create([
                'product_condition_id' => $condition->id,
                'imei' => $imei,
                'battery_health' => $batteryHealth,
                'cost_price' => $this->estimated_value,
                'status' => DeviceUnitStatusEnum::AVAILABLE,
                'trade_in_valuation_id' => $this->id,
                'notes' => "Trade-in from {$customerName} ({$this->device_brand} {$this->device_model})",
            ]);

            $this->forceFill([
                'product_condition_id' => $condition->id,
                'status' => TradeInStatusEnum::COMPLETED,
                'stock_ingested_at' => now(),
            ])->saveQuietly();

            return $movement;
        });
    }
}
