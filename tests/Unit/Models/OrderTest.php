<?php

namespace Tests\Unit\Models;

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
        $this->assertSame('pending', $order->status);
    }
}
