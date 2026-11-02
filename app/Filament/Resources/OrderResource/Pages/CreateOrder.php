<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Enums\OrderStatusEnum;
use App\Enums\StockMovementTypeEnum;
use App\Filament\Resources\OrderResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function afterCreate(): void
    {
        $order = $this->record;

        DB::transaction(function () use ($order) {
            $order->loadMissing('items.productCondition');

            $total = 0;
            foreach ($order->items as $item) {
                $total += $item->quantity * $item->price_at_purchase;

                if ($item->productCondition && $order->status !== OrderStatusEnum::CANCELLED) {
                    $item->productCondition->recordStockMovement(
                        type: StockMovementTypeEnum::SALE,
                        quantity: -$item->quantity,
                        note: "Staff manual order #{$order->id}",
                        createdBy: auth()->id()
                    );
                }
            }

            $order->forceFill([
                'total' => $total,
                'restocked_at' => $order->status === OrderStatusEnum::CANCELLED ? now() : null,
            ])->saveQuietly();
        });
    }
}
