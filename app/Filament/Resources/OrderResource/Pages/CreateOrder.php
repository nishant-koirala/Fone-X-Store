<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Filament\Resources\OrderResource;
use App\Models\Order;
use App\Models\ProductCondition;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;
use Filament\Support\Exceptions\Halt;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    /**
     * Automatically confirm staff-created admin orders so they are never auto-cancelled by expiry cron.
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['confirmed_at'] = now();

        return $data;
    }

    /**
     * afterCreate() is called after Filament has:
     *   1. Created the Order record (handleRecordCreation)
     *   2. Persisted the Repeater items via saveRelationships()
     *
     * The AdminPanelProvider has ->databaseTransactions() enabled, so steps 1, 2,
     * and this afterCreate hook all run inside a single outer DB transaction.
     *
     * Inside that outer transaction we re-fetch each ProductCondition with
     * lockForUpdate so two concurrent staff requests cannot both commit against
     * the same last unit.  If stock is insufficient, we throw Halt — Filament
     * catches it, rolls back the outer transaction, and shows the notification.
     * The order row and item rows are never committed.
     */
    protected function afterCreate(): void
    {
        /** @var Order $order */
        $order = $this->record;
        $order->loadMissing('items.productCondition');

        // Collect condition IDs so we can lock all rows in one query
        $conditionIds = $order->items
            ->pluck('product_condition_id')
            ->filter()
            ->unique()
            ->values()
            ->all();

        // Lock the rows for the duration of the outer Filament transaction
        $conditions = ProductCondition::with('product')
            ->whereIn('id', $conditionIds)
            ->lockForUpdate()
            ->get()
            ->keyBy('id');

        $total = 0;

        foreach ($order->items as $item) {
            $condId = $item->product_condition_id;
            $qty = (int) $item->quantity;

            if (! isset($conditions[$condId])) {
                Notification::make()
                    ->title('Stock Error')
                    ->body('One of the selected products is no longer available.')
                    ->danger()
                    ->send();

                throw (new Halt)->rollBackDatabaseTransaction();
            }

            $condition = $conditions[$condId];

            // Real stock check against the locked row (form maxValue is UI-only)
            if ($condition->quantity_in_stock < $qty) {
                $label = $condition->product
                    ? "{$condition->product->brand} {$condition->product->name}"
                    : "Condition #{$condId}";

                Notification::make()
                    ->title('Insufficient Stock')
                    ->body("Only {$condition->quantity_in_stock} unit(s) of {$label} available.")
                    ->danger()
                    ->send();

                throw (new Halt)->rollBackDatabaseTransaction();
            }

            if ($order->status !== OrderStatusEnum::CANCELLED) {
                $condition->recordStockMovement(
                    type: StockMovementTypeEnum::SALE,
                    quantity: -$qty,
                    note: "Staff manual order #{$order->id}",
                    createdBy: auth()->id()
                );
            }

            $item->forceFill([
                'cost_price_at_purchase' => $condition->cost_price,
            ])->saveQuietly();

            $total += $qty * $item->price_at_purchase;
        }

        $order->forceFill([
            'total' => $total,
            'restocked_at' => $order->status === OrderStatusEnum::CANCELLED ? now() : null,
        ])->saveQuietly();
    }
}
