<?php

namespace Tests\Unit\Models;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Models\Review;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_belongs_to_category(): void
    {
        $category = Category::factory()->create(['name' => 'Flagships']);
        $product = Product::factory()->create(['category_id' => $category->id]);

        $this->assertInstanceOf(Category::class, $product->category);
        $this->assertEquals($category->id, $product->category->id);
    }

    public function test_product_has_many_conditions(): void
    {
        $product = Product::factory()->create();
        $condition1 = ProductCondition::factory()->create(['product_id' => $product->id, 'grade' => 'new']);
        $condition2 = ProductCondition::factory()->create(['product_id' => $product->id, 'grade' => 'A']);

        $this->assertCount(2, $product->conditions);
        $this->assertTrue($product->conditions->contains($condition1));
        $this->assertTrue($product->conditions->contains($condition2));
    }

    public function test_product_has_many_reviews(): void
    {
        $product = Product::factory()->create();
        $review = Review::factory()->create(['product_id' => $product->id]);

        $this->assertCount(1, $product->reviews);
        $this->assertTrue($product->reviews->contains($review));
    }

    public function test_product_casts_attributes_correctly(): void
    {
        $product = Product::factory()->create([
            'base_price' => '99999.50',
            'is_new' => 1,
            'is_visible' => 1,
            'is_featured' => 0,
            'gallery' => ['img1.jpg', 'img2.jpg'],
        ]);

        $this->assertSame('99999.50', (string) $product->base_price);
        $this->assertTrue($product->is_new);
        $this->assertTrue($product->is_visible);
        $this->assertFalse($product->is_featured);
        $this->assertIsArray($product->gallery);
        $this->assertEquals(['img1.jpg', 'img2.jpg'], $product->gallery);
    }

    public function test_product_fillable_protection(): void
    {
        $product = new Product();
        $this->assertContains('name', $product->getFillable());
        $this->assertContains('slug', $product->getFillable());
        $this->assertContains('base_price', $product->getFillable());
        $this->assertNotContains('id', $product->getFillable());
    }
}
