<?php

namespace App\Services\Inventory;

use App\Enums\DeviceUnitStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Exceptions\InsufficientStockException;
use App\Models\DeviceUnit;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class UnitInventoryService
{
    /**
     * Register a new device unit into inventory.
     * If created with AVAILABLE status, records +1 stock movement on the condition.
     *
     * @param  array<string, mixed>  $data
     */
    public function registerUnit(ProductCondition $condition, array $data, ?int $userId = null): DeviceUnit
    {
        return DB::transaction(function () use ($condition, $data, $userId): DeviceUnit {
            $status = isset($data['status'])
                ? ($data['status'] instanceof DeviceUnitStatusEnum ? $data['status'] : DeviceUnitStatusEnum::from((string) $data['status']))
                : DeviceUnitStatusEnum::AVAILABLE;

            $data['product_condition_id'] = $condition->id;
            $data['status'] = $status;

            if ($status === DeviceUnitStatusEnum::AVAILABLE) {
                $imei = ! empty($data['imei']) ? $data['imei'] : 'N/A';
                $condition->recordStockMovement(
                    type: StockMovementTypeEnum::RESTOCK,
                    quantity: 1,
                    note: "New device unit registered (IMEI: {$imei})",
                    createdBy: $userId
                );
            }

            return DeviceUnit::create($data);
        });
    }

    /**
     * Assign available units to an order item during sale.
     * For tracked conditions, assigns specific units or oldest available units.
     *
     * @param  array<int>|null  $specificUnitIds
     * @return Collection<int, DeviceUnit>
     *
     * @throws InsufficientStockException
     */
    public function assignUnitsToOrderItem(OrderItem $orderItem, ?array $specificUnitIds = null, ?int $userId = null): Collection
    {
        $condition = $orderItem->productCondition;

        // Untracked conditions do not assign units
        if (! $condition || ! $condition->tracks_units) {
            return collect();
        }

        $qty = (int) $orderItem->quantity;

        if (! empty($specificUnitIds)) {
            /** @var Collection<int, DeviceUnit> $units */
            $units = DeviceUnit::where('product_condition_id', $condition->id)
                ->whereIn('id', $specificUnitIds)
                ->where('status', DeviceUnitStatusEnum::AVAILABLE)
                ->lockForUpdate()
                ->get();

            if ($units->count() !== $qty) {
                throw new InsufficientStockException('One or more selected units are no longer available for purchase.');
            }
        } else {
            /** @var Collection<int, DeviceUnit> $units */
            $units = DeviceUnit::where('product_condition_id', $condition->id)
                ->where('status', DeviceUnitStatusEnum::AVAILABLE)
                ->orderBy('id', 'asc')
                ->limit($qty)
                ->lockForUpdate()
                ->get();

            if ($units->count() < $qty) {
                $label = $condition->product ? "{$condition->product->brand} {$condition->product->name}" : "Condition #{$condition->id}";
                throw new InsufficientStockException("Insufficient units available for {$label} (Grade: {$condition->grade->value}). Requested {$qty}, but only {$units->count()} available.");
            }
        }

        $now = now();
        foreach ($units as $unit) {
            $unit->update([
                'status' => DeviceUnitStatusEnum::SOLD,
                'order_item_id' => $orderItem->id,
                'sold_at' => $now,
            ]);
        }

        return $units;
    }

    /**
     * Restore sold units linked to an order back to available upon cancellation, delete, or expiry.
     * Idempotent: safe to call alongside order restock.
     */
    public function restockOrderUnits(Order $order, ?int $userId = null): void
    {
        $order->loadMissing('items');
        $itemIds = $order->items->pluck('id')->all();

        if (empty($itemIds)) {
            return;
        }

        /** @var Collection<int, DeviceUnit> $units */
        $units = DeviceUnit::whereIn('order_item_id', $itemIds)
            ->where('status', DeviceUnitStatusEnum::SOLD)
            ->lockForUpdate()
            ->get();

        foreach ($units as $unit) {
            $unit->update([
                'status' => DeviceUnitStatusEnum::AVAILABLE,
                'order_item_id' => null,
                'sold_at' => null,
            ]);
        }
    }

    /**
     * Mark a sold device unit as returned under customer guarantee.
     * Does NOT put the unit back on sale automatically.
     *
     * @throws \LogicException
     */
    public function markReturned(DeviceUnit $unit, ?string $reason = null, ?int $userId = null): void
    {
        if ($unit->status !== DeviceUnitStatusEnum::SOLD) {
            throw new \LogicException("Only sold units can be marked as returned. Current status: {$unit->status->value}.");
        }

        DB::transaction(function () use ($unit, $reason): void {
            /** @var DeviceUnit $locked */
            $locked = DeviceUnit::where('id', $unit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== DeviceUnitStatusEnum::SOLD) {
                throw new \LogicException("Only sold units can be marked as returned. Current status: {$locked->status->value}.");
            }

            $note = $reason ? ($locked->notes ? "{$locked->notes}\nReturn Reason: {$reason}" : "Return Reason: {$reason}") : $locked->notes;

            $locked->update([
                'status' => DeviceUnitStatusEnum::RETURNED,
                'notes' => $note,
            ]);

            $unit->status = DeviceUnitStatusEnum::RETURNED;
            $unit->notes = $note;
        });
    }

    /**
     * Return a previously returned device unit back into sellable stock.
     * Records +1 stock movement via StockMovement audit log.
     *
     * @throws \LogicException
     */
    public function returnToStock(DeviceUnit $unit, ?int $userId = null): void
    {
        if ($unit->status !== DeviceUnitStatusEnum::RETURNED) {
            throw new \LogicException("Only returned units can be returned to stock. Current status: {$unit->status->value}.");
        }

        DB::transaction(function () use ($unit, $userId): void {
            /** @var DeviceUnit $locked */
            $locked = DeviceUnit::where('id', $unit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== DeviceUnitStatusEnum::RETURNED) {
                throw new \LogicException("Only returned units can be returned to stock. Current status: {$locked->status->value}.");
            }

            $condition = $locked->productCondition;
            if ($condition) {
                $imei = ! empty($locked->imei) ? $locked->imei : 'N/A';
                $condition->recordStockMovement(
                    type: StockMovementTypeEnum::RESTOCK,
                    quantity: 1,
                    note: "Returned unit returned to stock (IMEI: {$imei})",
                    createdBy: $userId
                );
            }

            $locked->update([
                'status' => DeviceUnitStatusEnum::AVAILABLE,
                'order_item_id' => null,
                'sold_at' => null,
            ]);

            $unit->status = DeviceUnitStatusEnum::AVAILABLE;
            $unit->order_item_id = null;
            $unit->sold_at = null;
        });
    }

    /**
     * Handle manual status transitions from admin edit.
     * Adjusts condition stock counter accordingly.
     *
     * @throws \LogicException
     */
    public function transitionUnitStatus(DeviceUnit $unit, DeviceUnitStatusEnum $newStatus, ?string $note = null, ?int $userId = null): void
    {
        if ($unit->status === $newStatus) {
            return;
        }

        if ($unit->status === DeviceUnitStatusEnum::SOLD) {
            throw new \LogicException('Sold units cannot have their status edited directly. Use return actions instead.');
        }

        if ($newStatus === DeviceUnitStatusEnum::SOLD) {
            throw new \LogicException('Units cannot be manually marked as sold. They must be sold through orders.');
        }

        DB::transaction(function () use ($unit, $newStatus, $note, $userId): void {
            /** @var DeviceUnit $locked */
            $locked = DeviceUnit::where('id', $unit->id)->lockForUpdate()->firstOrFail();
            $oldStatus = $locked->status;

            if ($oldStatus === $newStatus) {
                return;
            }

            $condition = $locked->productCondition;
            $imei = ! empty($locked->imei) ? $locked->imei : 'N/A';

            // Changing away from AVAILABLE records -1 stock movement
            if ($oldStatus === DeviceUnitStatusEnum::AVAILABLE && $newStatus !== DeviceUnitStatusEnum::AVAILABLE) {
                if ($condition) {
                    $condition->recordStockMovement(
                        type: StockMovementTypeEnum::ADJUSTMENT,
                        quantity: -1,
                        note: "Unit marked {$newStatus->value} (IMEI: {$imei})".($note ? ": {$note}" : ''),
                        createdBy: $userId
                    );
                }
            }

            // Changing to AVAILABLE from defective/returned records +1 stock movement
            if ($oldStatus !== DeviceUnitStatusEnum::AVAILABLE && $newStatus === DeviceUnitStatusEnum::AVAILABLE) {
                if ($condition) {
                    $condition->recordStockMovement(
                        type: StockMovementTypeEnum::RESTOCK,
                        quantity: 1,
                        note: "Unit restored to available (IMEI: {$imei})",
                        createdBy: $userId
                    );
                }
            }

            $lockedNotes = $note ? ($locked->notes ? "{$locked->notes}\n{$note}" : $note) : $locked->notes;
            $locked->update([
                'status' => $newStatus,
                'notes' => $lockedNotes,
            ]);

            $unit->status = $newStatus;
            $unit->notes = $lockedNotes;
        });
    }

    /**
     * Delete a device unit.
     * Deleting a sold unit is strictly blocked.
     * Deleting an available unit records -1 stock movement.
     *
     * @throws \LogicException
     */
    public function deleteUnit(DeviceUnit $unit, ?int $userId = null): void
    {
        if ($unit->status === DeviceUnitStatusEnum::SOLD) {
            throw new \LogicException('Cannot delete a sold device unit. Sold units must be retained for sales and warranty audit trails.');
        }

        DB::transaction(function () use ($unit, $userId): void {
            /** @var DeviceUnit $locked */
            $locked = DeviceUnit::where('id', $unit->id)->lockForUpdate()->firstOrFail();

            if ($locked->status === DeviceUnitStatusEnum::SOLD) {
                throw new \LogicException('Cannot delete a sold device unit. Sold units must be retained for sales and warranty audit trails.');
            }

            if ($locked->status === DeviceUnitStatusEnum::AVAILABLE) {
                $condition = $locked->productCondition;
                if ($condition) {
                    $imei = ! empty($locked->imei) ? $locked->imei : 'N/A';
                    $condition->recordStockMovement(
                        type: StockMovementTypeEnum::ADJUSTMENT,
                        quantity: -1,
                        note: "Device unit deleted from inventory (IMEI: {$imei})",
                        createdBy: $userId
                    );
                }
            }

            $locked->delete();
        });
    }
}
