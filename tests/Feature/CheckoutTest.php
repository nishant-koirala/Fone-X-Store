<?php

namespace Tests\Feature;

use App\Mail\OrderPlaced;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $category = Category::create(['name' => 'Phones', 'slug' => 'phones']);
        $this->product = Product::create([
            'category_id' => $category->id,
            'name' => 'Test Phone',
            'slug' => 'test-phone',
            'brand' => 'TestBrand',
            'base_price' => 1000,
            'is_visible' => true,
        ]);

        $this->condition = ProductCondition::create([
            'product_id' => $this->product->id,
            'grade' => 'new',
            'price' => 1000,
            'quantity_in_stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_can_checkout_with_items()
    {
        Mail::fake();

        // Add to cart
        $this->withSession(['cart' => [
            $this->condition->id => ['quantity' => 2],
        ]]);

        $response = $this->post(route('checkout.store'), [
            'name' => 'John Doe',
            'phone' => '9800000000',
            'email' => 'john@example.com',
            'address' => 'Kathmandu, Nepal',
        ]);

        $response->assertRedirect();

        // Assert order created
        $this->assertDatabaseHas('orders', [
            'total' => 2000,
            'status' => 'pending',
        ]);

        // Assert customer created
        $this->assertDatabaseHas('customers', [
            'name' => 'John Doe',
            'phone' => '9800000000',
        ]);

        // Assert stock deducted
        $this->assertEquals(3, $this->condition->fresh()->quantity_in_stock);

        // Assert mail queued
        Mail::assertQueued(OrderPlaced::class);
    }
}
