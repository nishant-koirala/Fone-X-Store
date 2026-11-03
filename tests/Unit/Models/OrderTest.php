<?php

namespace Tests\Unit\Models;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_belongs_to_customer(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        $this->assertInstanceOf(Customer::class, $order->customer);
        $this->assertEquals($customer->id, $order->customer->id);
    }

    public function test_order_has_many_items(): void
    {
        $order = Order::factory()->create();
        $item1 = OrderItem::factory()->create(['order_id' => $order->id]);
        $item2 = OrderItem::factory()->create(['order_id' => $order->id]);

        $this->assertCount(2, $order->items);
        $this->assertTrue($order->items->contains($item1));
        $this->assertTrue($order->items->contains($item2));
    }

    public function test_order_item_belongs_to_order_and_condition(): void
    {
        $order = Order::factory()->create();
        $condition = ProductCondition::factory()->create();
        $item = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 2,
            'price_at_purchase' => 85000.00,
        ]);

        $this->assertEquals($order->id, $item->order->id);
        $this->assertEquals($condition->id, $item->productCondition->id);
        $this->assertSame(2, $item->quantity);
        $this->assertSame('85000.00', (string) $item->price_at_purchase);
    }

    public function test_order_status_and_total_casts(): void
    {
        $order = Order::factory()->create([
            'total' => '120000.50',
            'status' => 'pending',
        ]);

        $this->assertSame('120000.50', (string) $order->total);
        $this->assertSame(OrderStatusEnum::PENDING, $order->status);
        $this->assertSame('pending', $order->status->value);
    }

    public function test_order_soft_deletes_and_is_recoverable(): void
    {
        $order = Order::factory()->create();
        $orderId = $order->id;

        // Soft delete — should stamp deleted_at, not hard-delete
        $order->delete();

        $this->assertSoftDeleted('orders', ['id' => $orderId]);

        // Default query scope excludes soft-deleted rows
        $this->assertNull(Order::find($orderId));

        // withTrashed() includes them — so it can be recovered
        $found = Order::withTrashed()->find($orderId);
        $this->assertNotNull($found);
        $this->assertNotNull($found->deleted_at);

        // Restore brings it back to the default scope
        $found->restore();
        $this->assertNotNull(Order::find($orderId));
        $this->assertNull(Order::find($orderId)->deleted_at);
    }

    public function test_order_and_order_item_profit_calculation(): void
    {
        $order = Order::factory()->create();
        $condition = ProductCondition::factory()->create(['cost_price' => 40000]);

        $item1 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 2,
            'price_at_purchase' => 50000.00,
            'cost_price_at_purchase' => 40000.00,
        ]);

        $item2 = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 1,
            'price_at_purchase' => 60000.00,
            'cost_price_at_purchase' => 45000.00,
        ]);

        // item1: (50000 - 40000) * 2 = 20000
        $this->assertEquals(20000.0, $item1->profit);
        // item2: (60000 - 45000) * 1 = 15000
        $this->assertEquals(15000.0, $item2->profit);

        // order total profit: 20000 + 15000 = 35000
        $this->assertEquals(35000.0, $order->calculateProfit());
    }

    public function test_calculate_profit_returns_null_when_costs_unrecorded(): void
    {
        $order = Order::factory()->create();
        OrderItem::factory()->create([
            'order_id' => $order->id,
            'quantity' => 1,
            'price_at_purchase' => 50000.00,
            'cost_price_at_purchase' => null,
        ]);

        $this->assertNull($order->calculateProfit());
    }
}
