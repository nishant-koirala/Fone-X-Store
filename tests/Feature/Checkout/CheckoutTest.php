<?php

namespace Tests\Feature\Checkout;

use App\Mail\OrderPlaced;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkout_page_requires_non_empty_cart(): void
    {
        // Visiting checkout with empty cart redirects to cart index
        $response = $this->get(route('checkout.index'));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('warning');
    }

    public function test_checkout_page_displays_cart_summary_and_total(): void
    {
        $condition = ProductCondition::factory()->create([
            'price' => 75000,
            'quantity_in_stock' => 5,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 2]]);

        $response = $this->get(route('checkout.index'));

        $response->assertStatus(200);
        $response->assertViewIs('checkout.index');
        $this->assertEquals(150000, $response->viewData('total'));
        $this->assertCount(1, $response->viewData('cartItems'));
    }

    public function test_validation_for_every_checkout_field(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);
        $this->setCartSession([$condition->id => ['quantity' => 1]]);

        // 1. Missing required fields (name, phone, address)
        $responseRequired = $this->post(route('checkout.store'), []);
        $responseRequired->assertSessionHasErrors(['name', 'phone', 'address']);

        // 2. Invalid email format
        $responseEmail = $this->post(route('checkout.store'), [
            'name' => 'Valid Name',
            'phone' => '9812345678',
            'email' => 'invalid-email-format',
            'address' => 'Kathmandu, Nepal',
        ]);
        $responseEmail->assertSessionHasErrors(['email']);

        // 3. String max length violations
        $responseMax = $this->post(route('checkout.store'), [
            'name' => str_repeat('A', 256), // Max 255
            'phone' => str_repeat('9', 51), // Max 50
            'email' => str_repeat('a', 250).'@test.com', // Exceeds 255
            'address' => str_repeat('B', 1001), // Max 1000
        ]);
        $responseMax->assertSessionHasErrors(['name', 'phone', 'email', 'address']);
    }

    public function test_empty_cart_cannot_checkout_via_post(): void
    {
        $response = $this->post(route('checkout.store'), [
            'name' => 'Dipesh Shrestha',
            'phone' => '9841000000',
            'email' => 'dipesh@example.com',
            'address' => 'Baneshwor, Kathmandu',
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_successful_checkout_creates_customer_order_and_order_items(): void
    {
        Mail::fake();

        $cond1 = ProductCondition::factory()->create(['price' => 50000, 'quantity_in_stock' => 10]);
        $cond2 = ProductCondition::factory()->create(['price' => 80000, 'quantity_in_stock' => 10]);

        $this->setCartSession([
            $cond1->id => ['quantity' => 2], // 100,000
            $cond2->id => ['quantity' => 1], // 80,000
        ]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'Aayush Adhikari',
            'phone' => '9841234567',
            'email' => 'aayush@nepalmail.com',
            'address' => 'Pulchowk, Lalitpur, Nepal',
        ]);

        // Customer created
        $customer = Customer::where('phone', '9841234567')->first();
        $this->assertNotNull($customer);
        $this->assertEquals('Aayush Adhikari', $customer->name);
        $this->assertEquals('aayush@nepalmail.com', $customer->email);
        $this->assertEquals('Pulchowk, Lalitpur, Nepal', $customer->address);

        // Order created with pending status and correct total (180,000)
        $order = Order::where('customer_id', $customer->id)->first();
        $this->assertNotNull($order);
        $this->assertEquals('pending', $order->status);
        $this->assertEquals(180000, (float) $order->total);

        // Order items created with accurate price snapshots
        $this->assertCount(2, $order->items);
        $item1 = $order->items->firstWhere('product_condition_id', $cond1->id);
        $this->assertNotNull($item1);
        $this->assertEquals(2, $item1->quantity);
        $this->assertEquals(50000, (float) $item1->price_at_purchase);

        $item2 = $order->items->firstWhere('product_condition_id', $cond2->id);
        $this->assertNotNull($item2);
        $this->assertEquals(1, $item2->quantity);
        $this->assertEquals(80000, (float) $item2->price_at_purchase);

        // Stock was deducted exactly once via stock movements
        $this->assertEquals(8, $cond1->fresh()->quantity_in_stock);
        $this->assertEquals(9, $cond2->fresh()->quantity_in_stock);

        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $cond1->id,
            'type' => 'sale',
            'quantity' => -2,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_condition_id' => $cond2->id,
            'type' => 'sale',
            'quantity' => -1,
        ]);

        // Cart session is cleared
        $this->assertNull(session('cart'));

        // Redirects to confirmation page
        $response->assertRedirect(route('orders.confirmation', $order));

        // Mail queued
        Mail::assertQueued(OrderPlaced::class, fn ($mail) => $mail->hasTo('aayush@nepalmail.com'));
    }

    public function test_race_condition_and_oversell_protection_rolls_back_everything(): void
    {
        Mail::fake();

        $condition = ProductCondition::factory()->create([
            'price' => 60000,
            'quantity_in_stock' => 2,
        ]);

        // Put 2 items in cart
        $this->setCartSession([$condition->id => ['quantity' => 2]]);

        // Simulate another customer purchasing 1 item concurrently before this checkout submits
        $condition->update(['quantity_in_stock' => 1]);

        $response = $this->from(route('checkout.index'))->post(route('checkout.store'), [
            'name' => 'Late Customer',
            'phone' => '9811111111',
            'address' => 'Thamel, Kathmandu',
        ]);

        // Order must be rejected
        $response->assertRedirect(route('checkout.index'));
        $response->assertSessionHas('error');

        // Assert nothing partially written to DB: no order, no order items, stock untouched
        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
        $this->assertEquals(1, $condition->fresh()->quantity_in_stock);
        $this->assertDatabaseMissing('stock_movements', [
            'product_condition_id' => $condition->id,
            'type' => 'sale',
        ]);
    }

    public function test_double_submission_does_not_create_two_orders_or_deduct_stock_twice(): void
    {
        Mail::fake();

        $condition = ProductCondition::factory()->create([
            'price' => 50000,
            'quantity_in_stock' => 5,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 1]]);

        $payload = [
            'name' => 'Fast Clicker',
            'phone' => '9899999999',
            'address' => 'Patan, Nepal',
        ];

        // First click
        $response1 = $this->post(route('checkout.store'), $payload);
        $response1->assertRedirect();
        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals(4, $condition->fresh()->quantity_in_stock);

        // Immediate second click (session cart is now empty)
        $response2 = $this->post(route('checkout.store'), $payload);
        $response2->assertRedirect(route('cart.index'));
        $response2->assertSessionHas('error', 'Your cart is empty.');

        // Second click does NOT create a 2nd order or deduct stock again
        $this->assertDatabaseCount('orders', 1);
        $this->assertEquals(4, $condition->fresh()->quantity_in_stock);
    }

    public function test_order_confirmation_page_is_not_viewable_for_another_session_order(): void
    {
        $customer = Customer::factory()->create();
        $order = Order::factory()->create(['customer_id' => $customer->id]);

        // Without 'last_order_id' matching in session, redirected to home (IDOR protection)
        $responseUnauthorized = $this->get(route('orders.confirmation', $order));
        $responseUnauthorized->assertRedirect(route('home'));
        $responseUnauthorized->assertSessionHas('error');

        // With spoofed/different order ID in session, also rejected
        $responseSpoofed = $this->withSession(['last_order_id' => $order->id + 999])
            ->get(route('orders.confirmation', $order));
        $responseSpoofed->assertRedirect(route('home'));

        // With legitimate matching order ID in session, 200 OK
        $responseAuthorized = $this->withSession(['last_order_id' => $order->id])
            ->get(route('orders.confirmation', $order));
        $responseAuthorized->assertStatus(200);
        $responseAuthorized->assertViewIs('orders.confirmation');
        $responseAuthorized->assertSee((string) $order->id);
    }
}
