<?php

namespace Tests\Feature\Security;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_csrf_middleware_rejects_unverified_requests_with_token_mismatch(): void
    {
        $middleware = new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\PreventRequestForgery {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $request = \Illuminate\Http\Request::create('/checkout', 'POST', ['name' => 'Attack Bot']);
        $session = app('session')->driver();
        $session->start();
        $request->setLaravelSession($session);

        $this->expectException(\Illuminate\Session\TokenMismatchException::class);
        $middleware->handle($request, fn () => response('OK'));
    }

    public function test_csrf_middleware_allows_requests_with_valid_token(): void
    {
        $middleware = new class(app(), app('encrypter')) extends \Illuminate\Foundation\Http\Middleware\PreventRequestForgery {
            protected function runningUnitTests(): bool
            {
                return false;
            }
        };

        $session = app('session')->driver();
        $session->start();
        $token = $session->token();

        $request = \Illuminate\Http\Request::create('/checkout', 'POST', ['_token' => $token]);
        $request->setLaravelSession($session);

        $response = $middleware->handle($request, fn () => response('OK'));
        $this->assertEquals(200, $response->getStatusCode());
    }




    public function test_mass_assignment_protection_on_core_models(): void
    {
        // 1. Product
        $product = Product::create([
            'id' => 99999, // Should NOT overwrite auto-increment ID
            'name' => 'Safe Phone',
            'slug' => 'safe-phone',
            'brand' => 'SafeBrand',
            'base_price' => 50000,
            'category_id' => \App\Models\Category::factory()->create()->id,
            'is_visible' => true,
        ]);
        $this->assertNotEquals(99999, $product->id);

        // 2. User
        $user = User::create([
            'id' => 88888,
            'name' => 'John Regular',
            'email' => 'johnregular@gmail.com',
            'password' => 'secret123',
            'remember_token' => 'hacked_token',
        ]);
        $this->assertNotEquals(88888, $user->id);
        $this->assertNull($user->remember_token);

        // 3. Order
        $customer = Customer::factory()->create();
        $order = Order::create([
            'id' => 77777,
            'customer_id' => $customer->id,
            'status' => 'pending',
            'total' => 20000,
        ]);
        $this->assertNotEquals(77777, $order->id);
    }

    public function test_xss_payload_in_product_name_is_escaped_in_catalog_and_show(): void
    {
        $xssName = '<script>alert("xss-test")</script>Phone';
        $product = $this->createProductWithConditions(['name' => $xssName]);

        // Catalog index
        $indexResponse = $this->get(route('products.index'));
        $indexResponse->assertStatus(200);
        $indexResponse->assertDontSee($xssName, false); // Raw script tag must NOT exist
        $indexResponse->assertSee('&lt;script&gt;alert(&quot;xss-test&quot;)&lt;/script&gt;Phone', false);

        // Product show
        $showResponse = $this->get(route('products.show', $product->slug));
        $showResponse->assertStatus(200);
        $showResponse->assertDontSee($xssName, false);
    }

    public function test_xss_payload_in_review_is_escaped(): void
    {
        $product = $this->createProductWithConditions();

        $xssComment = '<img src=x onerror=alert("xss")>Great phone!';
        $this->post(route('products.reviews.store', $product), [
            'name' => 'Safe Reviewer',
            'rating' => 5,
            'comment' => $xssComment,
        ]);

        $review = $product->reviews()->first();
        $this->assertNotNull($review);
        // Approve review to test storefront render
        $review->update(['is_approved' => true]);

        $response = $this->get(route('products.show', $product->slug));
        $response->assertStatus(200);
        $response->assertDontSee($xssComment, false);
    }
}
