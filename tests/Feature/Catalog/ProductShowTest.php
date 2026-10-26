<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductShowTest extends TestCase
{
    use RefreshDatabase;

    public function test_loads_product_detail_page_successfully_for_valid_slug(): void
    {
        $product = $this->createProductWithConditions(['name' => 'OnePlus 12 Pro']);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertViewIs('products.show');
        $response->assertSee('OnePlus 12 Pro');
    }

    public function test_returns_404_for_invalid_slug(): void
    {
        $response = $this->get(route('products.show', 'non-existent-slug-12345'));

        $response->assertStatus(404);
    }

    public function test_returns_404_when_product_is_not_visible(): void
    {
        $product = $this->createProductWithConditions([
            'name' => 'Draft Unreleased Phone',
            'is_visible' => false,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(404);
    }

    public function test_shows_condition_selector_and_stock_details(): void
    {
        $product = Product::factory()->create(['name' => 'Samsung S24 Show Test', 'is_visible' => true]);
        $newCond = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'new',
            'price' => 140000,
            'quantity_in_stock' => 8,
        ]);
        $gradeACond = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'A',
            'price' => 120000,
            'quantity_in_stock' => 3,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Select Device Condition');
        $response->assertSee('Selected Device Price (NPR)');
        $response->assertSee('140000.00');

        $viewProduct = $response->viewData('product');
        $this->assertCount(2, $viewProduct->conditions);
        $this->assertEquals(8, $viewProduct->conditions->firstWhere('grade', 'new')->quantity_in_stock);
        $this->assertEquals(3, $viewProduct->conditions->firstWhere('grade', 'A')->quantity_in_stock);
    }


    public function test_shows_out_of_stock_state_when_all_conditions_zero(): void
    {
        $product = Product::factory()->create(['name' => 'Sold Out Phone', 'is_visible' => true]);
        ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => 'new',
            'quantity_in_stock' => 0,
        ]);

        $response = $this->get(route('products.show', $product->slug));

        $response->assertStatus(200);
        $response->assertSee('Out of Stock');
    }

    public function test_related_products_belong_to_same_category_and_exclude_current_product(): void
    {
        $category = Category::factory()->create();

        $mainProduct = $this->createProductWithConditions(['category_id' => $category->id, 'name' => 'Main Product']);
        $related1 = $this->createProductWithConditions(['category_id' => $category->id, 'name' => 'Related Sibling 1']);
        $related2 = $this->createProductWithConditions(['category_id' => $category->id, 'name' => 'Related Sibling 2']);

        $response = $this->get(route('products.show', $mainProduct->slug));

        $response->assertStatus(200);
        $relatedProducts = $response->viewData('relatedProducts');

        $this->assertFalse($relatedProducts->contains('id', $mainProduct->id));
        $this->assertTrue($relatedProducts->contains('id', $related1->id));
        $this->assertTrue($relatedProducts->contains('id', $related2->id));
    }

    public function test_can_submit_product_review(): void
    {
        $product = $this->createProductWithConditions();

        $response = $this->post(route('products.reviews.store', $product), [
            'name' => 'Ram Sharma',
            'rating' => 5,
            'comment' => 'Excellent phone, pristine condition and fast delivery!',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('reviews', [
            'product_id' => $product->id,
            'name' => 'Ram Sharma',
            'rating' => 5,
            'is_approved' => false,
        ]);
    }

    public function test_review_validation_rejects_invalid_rating(): void
    {
        $product = $this->createProductWithConditions();

        $response = $this->post(route('products.reviews.store', $product), [
            'name' => 'Ram Sharma',
            'rating' => 99, // Max is 5
            'comment' => 'Invalid rating review',
        ]);

        $response->assertSessionHasErrors(['rating']);
    }
}
