<?php

namespace App\Console\Commands;

use App\Enums\OrderStatusEnum;
use App\Models\Order;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

#[Signature('shop:expire-unconfirmed-orders {--hours= : Custom expiration hours} {--dry-run : Simulate cancellation without modifying database}')]
#[Description('Auto-cancel unconfirmed pending COD orders exceeding expiry threshold and return stock to inventory')]
class ExpireUnconfirmedOrders extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $hours = (int) ($this->option('hours') ?: config('shop.orders.pending_expiry_hours', 48));
        $isDryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subHours($hours);

        $this->info("Scanning for unconfirmed pending COD orders older than {$hours} hours (placed before {$cutoff->toDateTimeString()})...");

        $orders = Order::query()
            ->where('status', OrderStatusEnum::PENDING)
            ->whereNull('confirmed_at')
            ->whereNull('restocked_at')
            ->where('created_at', '<=', $cutoff)
            ->with(['customer', 'items.productCondition'])
            ->get();

        if ($orders->isEmpty()) {
            $this->info('No expired unconfirmed orders found.');

            return self::SUCCESS;
        }

        $this->warn("Found {$orders->count()} expired unconfirmed order(s).");

        $cancelledCount = 0;

        foreach ($orders as $order) {
            $customerName = $order->customer->name ?? $order->shipping_name ?? 'Guest';
            $itemsCount = $order->items->sum('quantity');

            if ($isDryRun) {
                $this->line("[DRY-RUN] Would cancel Order #{$order->id} (Customer: {$customerName}, Items: {$itemsCount}, Placed: {$order->created_at})");
                $cancelledCount++;

                continue;
            }

            // Transitioning status to CANCELLED triggers Order::booted() updated hook,
            // which automatically restocks all line items via recordStockMovement(RESTOCK).
            $order->update(['status' => OrderStatusEnum::CANCELLED]);

            Log::info("Auto-expired unconfirmed pending order #{$order->id} after {$hours} hours.", [
                'order_id' => $order->id,
                'customer' => $customerName,
                'items_restocked' => $itemsCount,
            ]);

            $this->line("✔ Cancelled Order #{$order->id} (Customer: {$customerName}, Restocked {$itemsCount} items).");
            $cancelledCount++;
        }

        $actionWord = $isDryRun ? 'simulated cancellation for' : 'successfully cancelled and restocked';
        $this->info("Done: {$actionWord} {$cancelledCount} order(s).");

        return self::SUCCESS;
    }
}
