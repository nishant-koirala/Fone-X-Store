<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\Review;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SetupValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_factories_create_valid_records(): void
    {
        $user = User::factory()->create();
        $this->assertDatabaseHas('users', ['id' => $user->id]);

        $category = Category::factory()->create();
        $this->assertDatabaseHas('categories', ['id' => $category->id]);

        $product = Product::factory()->create(['category_id' => $category->id]);
        $this->assertDatabaseHas('products', ['id' => $product->id]);

        $condition = ProductCondition::factory()->create(['product_id' => $product->id]);
        $this->assertDatabaseHas('product_conditions', ['id' => $condition->id]);

        $customer = Customer::factory()->create();
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);

        $order = Order::factory()->create(['customer_id' => $customer->id]);
        $this->assertDatabaseHas('orders', ['id' => $order->id]);

        $orderItem = OrderItem::factory()->create([
            'order_id' => $order->id,
            'product_condition_id' => $condition->id,
        ]);
        $this->assertDatabaseHas('order_items', ['id' => $orderItem->id]);

        $stockMovement = StockMovement::factory()->create([
            'product_condition_id' => $condition->id,
            'created_by' => $user->id,
        ]);
        $this->assertDatabaseHas('stock_movements', ['id' => $stockMovement->id]);

        $review = Review::factory()->create(['product_id' => $product->id]);
        $this->assertDatabaseHas('reviews', ['id' => $review->id]);

        $post = Post::factory()->create();
        $this->assertDatabaseHas('posts', ['id' => $post->id]);
    }

    public function test_helper_trait_creates_product_with_conditions_and_stock(): void
    {
        $product = $this->createProductWithConditions([
            'name' => 'Custom Flagship',
            'base_price' => 150000,
        ]);

        $this->assertDatabaseHas('products', ['name' => 'Custom Flagship']);
        $this->assertCount(3, $product->conditions);
        $this->assertTrue($product->conditions->sum('quantity_in_stock') > 0);
    }

    public function test_helper_trait_logs_in_as_admin(): void
    {
        $admin = $this->loginAsAdmin();
        $this->assertAuthenticatedAs($admin);
    }
}
