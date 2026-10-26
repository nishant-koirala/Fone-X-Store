<?php

namespace Tests\Feature\Smoke;

use App\Models\Category;
use App\Models\Post;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicRoutesSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_all_public_get_routes_respond_with_successful_status(): void
    {
        $category = Category::factory()->create(['name' => 'Flagship', 'slug' => 'flagship']);
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'slug' => 'smoke-test-phone',
            'is_visible' => true,
        ]);
        ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'new',
            'quantity_in_stock' => 5,
        ]);

        $post = Post::factory()->create([
            'slug' => 'smoke-test-guide',
            'is_published' => true,
        ]);

        $publicRoutes = [
            'home' => '/',
            'products_index' => '/products',
            'product_show' => '/products/'.$product->slug,
            'wishlist' => '/wishlist',
            'sitemap' => '/sitemap.xml',
            'blog_index' => '/blog',
            'blog_show' => '/blog/'.$post->slug,
            'cart' => '/cart',
            'trade_in' => '/trade-in',
            'health_check' => '/up',
        ];

        foreach ($publicRoutes as $name => $uri) {
            $response = $this->get($uri);

            $this->assertLessThan(
                500,
                $response->status(),
                "Route [{$name}] ({$uri}) returned server error {$response->status()}."
            );
            $response->assertSuccessful();
        }
    }

    public function test_checkout_redirects_when_cart_is_empty(): void
    {
        $response = $this->get(route('checkout.index'));
        $response->assertRedirect(route('cart.index'));
    }

    public function test_checkout_page_loads_when_cart_has_items(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'quantity_in_stock' => 5,
            'price' => 50000,
        ]);

        $response = $this->withSession([
            'cart' => [
                $condition->id => [
                    'condition_id' => $condition->id,
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'grade' => $condition->grade,
                    'price' => $condition->price,
                    'quantity' => 1,
                ],
            ],
        ])->get(route('checkout.index'));

        $response->assertOk();
    }
}
