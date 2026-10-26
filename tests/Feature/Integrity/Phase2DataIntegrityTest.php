<?php

namespace Tests\Feature\Integrity;

use App\Mail\AdminTradeInAlert;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\StockMovement;
use App\Models\TradeInValuation;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\InteractsWithShop;
use Tests\TestCase;

class Phase2DataIntegrityTest extends TestCase
{
    use InteractsWithShop;
    use RefreshDatabase;

    public function test_products_and_conditions_use_soft_deletes(): void
    {
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        $product->delete();
        $this->assertSoftDeleted('products', ['id' => $product->id]);

        $condition->delete();
        $this->assertSoftDeleted('product_conditions', ['id' => $condition->id]);
    }

    public function test_order_items_restrict_deletion_of_referenced_product_condition(): void
    {
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        $order = Order::factory()->create();
        OrderItem::create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 1,
            'price_at_purchase' => $condition->price,
        ]);

        $this->expectException(QueryException::class);
        // Force delete attempts actual DELETE query, which triggers foreign key constraint
        $condition->forceDelete();
    }

    public function test_record_stock_movement_rejects_negative_stock(): void
    {
        $condition = $this->createCondition(['quantity_in_stock' => 3]);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot reduce stock below zero');

        $condition->recordStockMovement('sale', -5);
    }

    public function test_record_stock_movement_validates_quantity_signs_by_type(): void
    {
        $condition = $this->createCondition(['quantity_in_stock' => 10]);

        // Sale must be negative
        try {
            $condition->recordStockMovement('sale', 2);
            $this->fail('Expected InvalidArgumentException for positive sale');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('Sale stock movement must have a negative quantity', $e->getMessage());
        }

        // Restock must be positive
        try {
            $condition->recordStockMovement('restock', -3);
            $this->fail('Expected InvalidArgumentException for negative restock');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('restock stock movement must have a positive quantity', $e->getMessage());
        }

        // Exchange_in must be positive
        try {
            $condition->recordStockMovement('exchange_in', -1);
            $this->fail('Expected InvalidArgumentException for negative exchange_in');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('exchange_in stock movement must have a positive quantity', $e->getMessage());
        }

        // Zero quantity rejected
        try {
            $condition->recordStockMovement('adjustment', 0);
            $this->fail('Expected InvalidArgumentException for zero quantity');
        } catch (\InvalidArgumentException $e) {
            $this->assertStringContainsString('quantity cannot be zero', $e->getMessage());
        }

        // Adjustment accepts both positive and negative
        $condition->recordStockMovement('adjustment', 2, 'Found extra inventory');
        $this->assertEquals(12, $condition->fresh()->quantity_in_stock);

        $condition->recordStockMovement('adjustment', -2, 'Damaged items written off');
        $this->assertEquals(10, $condition->fresh()->quantity_in_stock);
    }

    public function test_cancelled_order_restocks_items_via_stock_movements_once_only(): void
    {
        $condition = $this->createCondition(['quantity_in_stock' => 10]);

        $order = Order::factory()->create(['status' => 'pending']);
        OrderItem::create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 3,
            'price_at_purchase' => $condition->price,
        ]);

        // Deduct initial sale
        $condition->recordStockMovement('sale', -3, "Order #{$order->id} sale");
        $this->assertEquals(7, $condition->fresh()->quantity_in_stock);

        // Cancel order -> should trigger restock
        $order->update(['status' => 'cancelled']);

        $this->assertEquals(10, $condition->fresh()->quantity_in_stock);
        $this->assertNotNull($order->fresh()->restocked_at);

        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'restock',
            'quantity' => 3,
            'note' => "Automatic restock from cancelled Order #{$order->id}",
        ]);

        $movementCount = StockMovement::where('product_condition_id', $condition->id)
            ->where('type', 'restock')
            ->count();
        $this->assertEquals(1, $movementCount);

        // Change status back and forth -> must NOT restock a second time
        $order->update(['status' => 'pending']);
        $order->update(['status' => 'cancelled']);

        $this->assertEquals(10, $condition->fresh()->quantity_in_stock);
        $newMovementCount = StockMovement::where('product_condition_id', $condition->id)
            ->where('type', 'restock')
            ->count();
        $this->assertEquals(1, $newMovementCount);
    }

    public function test_cannot_add_invisible_product_to_cart(): void
    {
        $category = Category::factory()->create();
        $invisibleProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_visible' => false,
        ]);

        $condition = ProductCondition::factory()->create([
            'product_id' => $invisibleProduct->id,
            'quantity_in_stock' => 5,
        ]);

        $response = $this->post('/cart/add', [
            'product_condition_id' => $condition->id,
            'quantity' => 1,
        ]);

        $response->assertSessionHas('error', 'This product is currently unavailable.');
        $this->assertEmpty(session('cart', []));
    }

    public function test_cannot_checkout_with_invisible_product_in_cart(): void
    {
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'Hidden Gadget',
            'is_visible' => false,
        ]);

        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'quantity_in_stock' => 5,
        ]);

        $response = $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Attempted Buyer',
            'phone' => '9841888999',
            'address' => 'Kathmandu',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('no longer available', (string) session('error'));
        $this->assertDatabaseMissing('orders', ['shipping_name' => 'Attempted Buyer']);
    }

    public function test_trade_in_mail_failure_does_not_break_submission(): void
    {
        Mail::shouldReceive('to')
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->andThrow(new \RuntimeException('Mail server unreachable'));

        $response = $this->post('/trade-in', [
            'device_brand' => 'Google',
            'device_model' => 'Pixel 7',
            'condition' => 'good',
            'customer_name' => 'Mail Resilient Customer',
            'customer_phone' => '9841555444',
            'customer_email' => 'customer@test.com',
        ]);

        $valuation = TradeInValuation::latest()->first();
        $this->assertNotNull($valuation);
        $this->assertEquals('Pixel 7', $valuation->device_model);
        $response->assertRedirect(route('trade-in.confirmation', $valuation));
    }

    public function test_admin_trade_in_alert_implements_should_queue(): void
    {
        $this->assertTrue(is_subclass_of(AdminTradeInAlert::class, ShouldQueue::class));
    }

    public function test_customers_phone_has_unique_constraint(): void
    {
        Customer::create([
            'name' => 'First Customer',
            'phone' => '9841009900',
            'email' => 'first@test.com',
            'address' => 'Kathmandu',
        ]);

        $this->expectException(QueryException::class);
        Customer::create([
            'name' => 'Duplicate Phone Customer',
            'phone' => '9841009900',
            'email' => 'second@test.com',
            'address' => 'Lalitpur',
        ]);
    }
}
