<?php

namespace Tests\Feature\Security;

use App\Models\Customer;
use App\Models\Order;
use App\Models\TradeInValuation;
use App\Models\User;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\Support\InteractsWithShop;
use Tests\TestCase;

class Phase1SecurityTest extends TestCase
{
    use InteractsWithShop;
    use RefreshDatabase;

    public function test_user_implements_filament_user_and_must_verify_email(): void
    {
        $this->assertTrue(is_subclass_of(User::class, FilamentUser::class));
        $this->assertTrue(is_subclass_of(User::class, MustVerifyEmail::class));
    }

    public function test_can_access_panel_returns_true_only_when_is_admin_is_true(): void
    {
        $regularUser = User::factory()->create();
        $this->assertFalse($regularUser->canAccessPanel(filament()->getCurrentPanel() ?? filament()->getPanel('admin')));

        $adminUser = User::factory()->admin()->create();
        $this->assertTrue($adminUser->canAccessPanel(filament()->getCurrentPanel() ?? filament()->getPanel('admin')));
    }

    public function test_regular_user_cannot_access_filament_admin(): void
    {
        $customer = User::factory()->create();
        $this->actingAs($customer);

        $response = $this->get('/admin');
        $response->assertStatus(403);
    }

    public function test_admin_user_can_access_filament_admin(): void
    {
        $admin = $this->loginAsAdmin();

        $response = $this->get('/admin');
        $response->assertStatus(200);
        $response->assertSee('FoneX Store Admin');
    }

    public function test_is_admin_is_not_mass_assignable(): void
    {
        $user = User::create([
            'name' => 'Sneaky User',
            'email' => 'sneaky@test.com',
            'password' => bcrypt('password123'),
            'is_admin' => true,
        ]);

        $this->assertFalse((bool) $user->fresh()->is_admin);
    }

    public function test_make_admin_command_promotes_user(): void
    {
        $user = User::factory()->create(['email' => 'promote@test.com']);
        $this->assertFalse((bool) $user->is_admin);

        $this->artisan('user:make-admin promote@test.com')
            ->expectsOutputToContain('promoted to administrator')
            ->assertExitCode(0);

        $this->assertTrue((bool) $user->fresh()->is_admin);
    }

    public function test_make_admin_command_fails_for_nonexistent_email(): void
    {
        $this->artisan('user:make-admin missing@test.com')
            ->expectsOutputToContain('User not found with email')
            ->assertExitCode(1);
    }

    public function test_verified_user_has_order_and_trade_in_linked_by_user_id(): void
    {
        Mail::fake();

        $user = User::factory()->create(['email_verified_at' => now()]);
        $this->actingAs($user);

        $product = $this->createProductWithConditions(['name' => 'iPhone 15 Pro']);
        $condition = $product->conditions->first();

        // Place Order
        $response = $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Verified Buyer',
            'phone' => '9841112233',
            'email' => 'buyer@test.com',
            'address' => 'Thamel, Kathmandu',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals($user->id, $order->user_id);
        $this->assertEquals('Verified Buyer', $order->shipping_name);
        $this->assertEquals('9841112233', $order->shipping_phone);
        $this->assertEquals('buyer@test.com', $order->shipping_email);
        $this->assertEquals('Thamel, Kathmandu', $order->shipping_address);

        // Submit Trade-in
        $this->post('/trade-in', [
            'device_brand' => 'Apple',
            'device_model' => 'iPhone 12',
            'condition' => 'good',
            'customer_name' => 'Verified Buyer',
            'customer_phone' => '9841112233',
            'customer_email' => 'buyer@test.com',
        ]);

        $valuation = TradeInValuation::latest()->first();
        $this->assertNotNull($valuation);
        $this->assertEquals($user->id, $valuation->user_id);
    }

    public function test_unverified_user_does_not_have_order_or_trade_in_linked(): void
    {
        Mail::fake();

        $unverifiedUser = User::factory()->unverified()->create();
        $this->actingAs($unverifiedUser);

        $product = $this->createProductWithConditions(['name' => 'iPhone 14']);
        $condition = $product->conditions->first();

        $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Unverified Customer',
            'phone' => '9841999888',
            'email' => 'unverified@test.com',
            'address' => 'Patan, Lalitpur',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertNull($order->user_id);

        $this->post('/trade-in', [
            'device_brand' => 'Samsung',
            'device_model' => 'Galaxy S21',
            'condition' => 'good',
            'customer_name' => 'Unverified Customer',
            'customer_phone' => '9841999888',
            'customer_email' => 'unverified@test.com',
        ]);

        $valuation = TradeInValuation::latest()->first();
        $this->assertNotNull($valuation);
        $this->assertNull($valuation->user_id);
    }

    public function test_checkout_does_not_overwrite_existing_customer_data_on_phone_match(): void
    {
        Mail::fake();

        $originalCustomer = Customer::create([
            'phone' => '9841223344',
            'name' => 'Original Customer',
            'email' => 'original@test.com',
            'address' => 'Original Address, Pokhara',
        ]);

        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Different Person',
            'phone' => '9841223344',
            'email' => 'different@test.com',
            'address' => 'Different Address, Kathmandu',
        ]);

        $originalCustomer->refresh();
        $this->assertEquals('Original Customer', $originalCustomer->name);
        $this->assertEquals('original@test.com', $originalCustomer->email);
        $this->assertEquals('Original Address, Pokhara', $originalCustomer->address);

        $order = Order::latest()->first();
        $this->assertEquals('Different Person', $order->shipping_name);
        $this->assertEquals('different@test.com', $order->shipping_email);
        $this->assertEquals('Different Address, Kathmandu', $order->shipping_address);
    }

    public function test_dashboard_queries_strictly_by_user_id_not_email(): void
    {
        $user = User::factory()->create(['email' => 'alice@test.com', 'email_verified_at' => now()]);

        // Create an unlinked order with Alice's email
        $customer = Customer::create([
            'name' => 'Alice Leak Test',
            'email' => 'alice@test.com',
            'phone' => '9841777888',
            'address' => 'Kathmandu',
        ]);

        $unlinkedOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => null,
            'shipping_name' => 'Alice Leak Test',
            'shipping_email' => 'alice@test.com',
        ]);

        $unlinkedTradeIn = TradeInValuation::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => null,
            'device_model' => 'LeakDeviceModel',
        ]);

        $this->actingAs($user);
        $response = $this->get('/dashboard');
        $response->assertStatus(200);
        $response->assertDontSee('#'.str_pad((string) $unlinkedOrder->id, 6, '0', STR_PAD_LEFT));
        $response->assertDontSee('LeakDeviceModel');

        // Link an order and trade-in directly to Alice
        $linkedOrder = Order::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'shipping_name' => 'Alice Verified',
        ]);

        $linkedTradeIn = TradeInValuation::factory()->create([
            'customer_id' => $customer->id,
            'user_id' => $user->id,
            'device_model' => 'VisibleLinkedDevice',
        ]);

        $response2 = $this->get('/dashboard');
        $response2->assertStatus(200);
        $response2->assertSee('#'.str_pad((string) $linkedOrder->id, 6, '0', STR_PAD_LEFT));
        $response2->assertSee('VisibleLinkedDevice');
    }

    public function test_trade_in_honeypot_drops_submission_silently(): void
    {
        Mail::fake();

        $response = $this->post('/trade-in', [
            'device_brand' => 'SpamBrand',
            'device_model' => 'SpamModel',
            'condition' => 'good',
            'customer_name' => 'Spammer',
            'customer_phone' => '9800000000',
            'secondary_address' => 'I am a spam bot',
        ]);

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('trade_in_valuations', ['device_brand' => 'SpamBrand']);
        Mail::assertNothingSent();
    }

    public function test_review_honeypot_drops_submission_silently(): void
    {
        $product = $this->createProductWithConditions();

        $response = $this->post(route('products.reviews.store', $product), [
            'name' => 'Bot Reviewer',
            'rating' => 5,
            'comment' => 'Spam review content',
            'author_notes' => 'I am a bot',
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseMissing('reviews', ['comment' => 'Spam review content']);
    }

    public function test_checkout_mail_failure_does_not_fail_committed_order(): void
    {
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        Mail::shouldReceive('to')
            ->andReturnSelf();
        Mail::shouldReceive('send')
            ->andThrow(new \RuntimeException('SMTP Connection Timeout'));

        $initialStock = $condition->quantity_in_stock;

        $response = $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Resilient Orderer',
            'phone' => '9841665544',
            'email' => 'resilient@test.com',
            'address' => 'Bhaktapur, Nepal',
        ]);

        $order = Order::latest()->first();
        $this->assertNotNull($order);
        $this->assertEquals('Resilient Orderer', $order->shipping_name);
        $response->assertRedirect(route('orders.confirmation', $order));

        $this->assertEquals($initialStock - 1, $condition->fresh()->quantity_in_stock);
        $this->assertNull(session('cart'));
    }

    public function test_insufficient_stock_exception_shows_friendly_message(): void
    {
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();
        $condition->update(['quantity_in_stock' => 1]);

        $response = $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 5],
            ],
        ])->post('/checkout', [
            'name' => 'High Demand Buyer',
            'phone' => '9841999111',
            'address' => 'Kathmandu, Nepal',
        ]);

        $response->assertSessionHas('error');
        $this->assertStringContainsString('insufficient', strtolower((string) session('error')));
    }

    public function test_design_demo_route_is_removed(): void
    {
        $response = $this->get('/design-demo');
        $response->assertStatus(404);
    }

    public function test_checkout_route_is_throttled_at_5_per_minute(): void
    {
        Mail::fake();
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        for ($i = 1; $i <= 5; $i++) {
            $response = $this->withSession([
                'cart' => [
                    $condition->id => ['quantity' => 1],
                ],
            ])->post('/checkout', [
                'name' => "Throttle User {$i}",
                'phone' => "984100000{$i}",
                'address' => 'Kathmandu',
            ]);

            $this->assertNotEquals(429, $response->status(), "Request {$i} was unexpectedly rate limited.");
        }

        // 6th request should be throttled
        $response = $this->withSession([
            'cart' => [
                $condition->id => ['quantity' => 1],
            ],
        ])->post('/checkout', [
            'name' => 'Throttle User 6',
            'phone' => '9841000006',
            'address' => 'Kathmandu',
        ]);

        $response->assertStatus(429);
    }

    public function test_trade_in_route_is_throttled_at_3_per_minute(): void
    {
        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post('/trade-in', [
                'device_brand' => 'Brand',
                'device_model' => "Model {$i}",
                'condition' => 'good',
                'customer_name' => 'TradeIn User',
                'customer_phone' => "984100001{$i}",
            ]);

            $this->assertNotEquals(429, $response->status(), "Request {$i} was unexpectedly rate limited.");
        }

        // 4th request should be throttled
        $response = $this->post('/trade-in', [
            'device_brand' => 'Brand',
            'device_model' => 'Model 4',
            'condition' => 'good',
            'customer_name' => 'TradeIn User',
            'customer_phone' => '9841000014',
        ]);

        $response->assertStatus(429);
    }

    public function test_review_route_is_throttled_at_3_per_minute(): void
    {
        $product = $this->createProductWithConditions();

        for ($i = 1; $i <= 3; $i++) {
            $response = $this->post(route('products.reviews.store', $product), [
                'name' => "Reviewer {$i}",
                'rating' => 5,
                'comment' => "Review content {$i}",
            ]);

            $this->assertNotEquals(429, $response->status(), "Request {$i} was unexpectedly rate limited.");
        }

        // 4th request should be throttled
        $response = $this->post(route('products.reviews.store', $product), [
            'name' => 'Reviewer 4',
            'rating' => 5,
            'comment' => 'Review content 4',
        ]);

        $response->assertStatus(429);
    }

    public function test_cart_add_route_is_throttled_at_30_per_minute(): void
    {
        $product = $this->createProductWithConditions();
        $condition = $product->conditions->first();

        for ($i = 1; $i <= 30; $i++) {
            $response = $this->post('/cart/add', [
                'condition_id' => $condition->id,
                'quantity' => 1,
            ]);

            $this->assertNotEquals(429, $response->status(), "Request {$i} was unexpectedly rate limited.");
        }

        // 31st request should be throttled
        $response = $this->post('/cart/add', [
            'condition_id' => $condition->id,
            'quantity' => 1,
        ]);

        $response->assertStatus(429);
    }
}
