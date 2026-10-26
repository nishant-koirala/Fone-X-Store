<?php

namespace Tests\Feature\Smoke;

use App\Mail\OrderPlaced;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EndToEndOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_end_to_end_customer_order_journey(): void
    {
        Mail::fake();

        // 1. Arrange Catalog
        $category = Category::factory()->create(['name' => 'iPhones', 'slug' => 'iphones']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => 'iPhone 15 Pro Max',
            'slug' => 'iphone-15-pro-max',
            'base_price' => 175000,
            'is_visible' => true,
        ]);

        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'new',
            'price' => 185000,
            'original_price' => 195000,
            'quantity_in_stock' => 5,
        ]);

        // 2. Step: Customer visits storefront homepage
        $homeResponse = $this->get(route('home'));
        $homeResponse->assertOk();
        $homeResponse->assertSee('iPhone 15 Pro Max');

        // 3. Step: Customer views product detail page
        $productResponse = $this->get(route('products.show', $product->slug));
        $productResponse->assertOk();
        $productResponse->assertSee('185000');

        // 4. Step: Customer adds 2 units of the condition to cart
        $cartAddResponse = $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 2,
        ]);
        $cartAddResponse->assertRedirect(route('cart.index'));
        $cartAddResponse->assertSessionHas('cart');

        // 5. Step: Customer views cart page
        $cartIndexResponse = $this->get(route('cart.index'));
        $cartIndexResponse->assertOk();
        $cartIndexResponse->assertSee('iPhone 15 Pro Max');
        $cartIndexResponse->assertSee('370,000'); // 185,000 * 2

        // 6. Step: Customer proceeds to checkout
        $checkoutIndexResponse = $this->get(route('checkout.index'));
        $checkoutIndexResponse->assertOk();

        // 7. Step: Customer submits checkout order form
        $orderPayload = [
            'name' => 'Aayush Adhikari',
            'email' => 'aayush.adhikari@example.com',
            'phone' => '9841122334',
            'address' => 'Jhamsikhel Road, Ward 3, Lalitpur',
            'notes' => 'Please ring the bell upon arrival',
        ];

        $checkoutPostResponse = $this->post(route('checkout.store'), $orderPayload);
        $checkoutPostResponse->assertSessionHasNoErrors();

        // Verify order exists
        $order = Order::latest()->first();
        $this->assertNotNull($order, 'Order should be created in database');
        $checkoutPostResponse->assertRedirect(route('orders.confirmation', $order));

        // 8. Step: Customer views confirmation page
        $confirmationResponse = $this->get(route('orders.confirmation', $order));
        $confirmationResponse->assertOk();
        $confirmationResponse->assertSee("Order #{$order->id}");
        $confirmationResponse->assertSee('Aayush Adhikari');

        // 9. Step: Database assertions
        // Customer record
        $this->assertDatabaseHas('customers', [
            'phone' => '9841122334',
            'email' => 'aayush.adhikari@example.com',
            'name' => 'Aayush Adhikari',
        ]);

        // Order record
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'customer_id' => $order->customer_id,
            'status' => 'pending',
            'total' => 370000,
        ]);

        // Order Item record
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
            'quantity' => 2,
            'price_at_purchase' => 185000,
        ]);

        // Stock deduction & movement tracking
        $this->assertEquals(3, $condition->fresh()->quantity_in_stock, 'Stock should decrease from 5 to 3');

        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $condition->id,
            'quantity' => -2,
            'type' => 'sale',
            'note' => "Customer sale for Order #{$order->id}",
        ]);

        // 10. Step: Email confirmation queued
        Mail::assertQueued(OrderPlaced::class, function (OrderPlaced $mail) use ($order) {
            return $mail->order->id === $order->id && $mail->hasTo('aayush.adhikari@example.com');
        });

        // 11. Step: Admin panel access - Admin views the order in Filament
        $admin = User::factory()->admin()->create([
            'email' => 'store-manager@fonexstore.test',
        ]);

        $this->actingAs($admin);
        $adminDashboardResponse = $this->get('/admin');
        $adminDashboardResponse->assertOk();

        $adminOrdersResponse = $this->get('/admin/orders');
        $adminOrdersResponse->assertOk();
        $adminOrdersResponse->assertSee('Aayush Adhikari');
    }
}
