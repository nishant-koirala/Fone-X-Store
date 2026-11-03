<?php

namespace Tests\Feature\Admin;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExpireUnconfirmedOrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_expires_unconfirmed_pending_orders_older_than_cutoff_and_restocks(): void
    {
        $customer = Customer::factory()->create();
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);

        // 1. Expired order (created 50 hours ago, unconfirmed)
        $expiredOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatusEnum::PENDING,
            'confirmed_at' => null,
            'created_at' => now()->subHours(50),
        ]);
        OrderItem::factory()->create([
            'order_id' => $expiredOrder->id,
            'product_condition_id' => $condition->id,
            'quantity' => 2,
            'price_at_purchase' => 50000,
        ]);

        // 2. Recent pending order (created 10 hours ago, unconfirmed) - should NOT expire
        $recentOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatusEnum::PENDING,
            'confirmed_at' => null,
            'created_at' => now()->subHours(10),
        ]);

        // 3. Old confirmed order (created 50 hours ago, but confirmed_at set) - should NOT expire
        $confirmedOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatusEnum::PENDING,
            'confirmed_at' => now()->subHours(49),
            'created_at' => now()->subHours(50),
        ]);

        $this->artisan('shop:expire-unconfirmed-orders')
            ->assertSuccessful()
            ->expectsOutputToContain('Found 1 expired unconfirmed order(s).');

        // Expired order should now be CANCELLED and restocked
        $this->assertEquals(OrderStatusEnum::CANCELLED, $expiredOrder->fresh()->status);
        $this->assertNotNull($expiredOrder->fresh()->restocked_at);
        $this->assertEquals(7, $condition->fresh()->quantity_in_stock); // 5 + 2 = 7

        // Other orders must remain PENDING
        $this->assertEquals(OrderStatusEnum::PENDING, $recentOrder->fresh()->status);
        $this->assertEquals(OrderStatusEnum::PENDING, $confirmedOrder->fresh()->status);
    }

    public function test_dry_run_option_simulates_without_modifying_database(): void
    {
        $customer = Customer::factory()->create();
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 3]);

        $order = Order::factory()->create([
            'customer_id' => $customer->id,
            'status' => OrderStatusEnum::PENDING,
            'confirmed_at' => null,
            'created_at' => now()->subHours(60),
        ]);
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 1,
            'price_at_purchase' => 30000,
        ]);

        $this->artisan('shop:expire-unconfirmed-orders --dry-run')
            ->assertSuccessful()
            ->expectsOutputToContain('[DRY-RUN]');

        // Status and stock must remain untouched
        $this->assertEquals(OrderStatusEnum::PENDING, $order->fresh()->status);
        $this->assertNull($order->fresh()->restocked_at);
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock);
    }
}
