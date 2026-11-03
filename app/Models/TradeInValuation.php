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
     * Only allowed when status is accepted.
     * Re-fetches with lockForUpdate to ensure idempotency across concurrent tabs.
     *
     * @throws \LogicException
     */
    public function ingestIntoStock(
        ProductCondition $condition,
        ?int $staffUserId = null,
        ?string $imei = null,
        ?int $batteryHealth = null,
        ?string $imei2 = null
    ): StockMovement {
        if ($this->stock_ingested_at !== null) {
            throw new \LogicException("Trade-in valuation #{$this->id} has already been ingested into inventory.");
        }

        if ($this->status !== TradeInStatusEnum::ACCEPTED) {
            throw new \LogicException("Only accepted trade-in valuations can be ingested into inventory. Current status: {$this->status->value}.");
        }

        return DB::transaction(function () use ($condition, $staffUserId, $imei, $batteryHealth, $imei2): StockMovement {
            /** @var TradeInValuation $locked */
            $locked = TradeInValuation::where('id', $this->id)->lockForUpdate()->firstOrFail();

            if ($locked->stock_ingested_at !== null) {
                throw new \LogicException("Trade-in valuation #{$this->id} has already been ingested into inventory.");
            }

            if ($locked->status !== TradeInStatusEnum::ACCEPTED) {
                throw new \LogicException("Only accepted trade-in valuations can be ingested into inventory. Current status: {$locked->status->value}.");
            }

            $customerName = $locked->customer ? $locked->customer->name : 'Customer';
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
                'imei2' => $imei2,
                'battery_health' => $batteryHealth,
                'cost_price' => $locked->estimated_value,
                'status' => DeviceUnitStatusEnum::AVAILABLE,
                'trade_in_valuation_id' => $locked->id,
                'notes' => "Trade-in from {$customerName} ({$locked->device_brand} {$locked->device_model})",
            ]);

            $now = now();
            $locked->forceFill([
                'product_condition_id' => $condition->id,
                'status' => TradeInStatusEnum::COMPLETED,
                'stock_ingested_at' => $now,
            ])->saveQuietly();

            $this->product_condition_id = $condition->id;
            $this->status = TradeInStatusEnum::COMPLETED;
            $this->stock_ingested_at = $now;

            return $movement;
        });
    }
}
