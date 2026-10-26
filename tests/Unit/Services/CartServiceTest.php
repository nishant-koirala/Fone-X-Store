<?php

namespace Tests\Unit\Services;

use App\Enums\GradeEnum;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use App\Services\Cart\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartServiceTest extends TestCase
{
    use RefreshDatabase;

    protected CartService $cartService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cartService = new CartService;
    }

    public function test_empty_cart_returns_zero_and_empty_items(): void
    {
        session(['cart' => []]);

        $this->assertTrue($this->cartService->isEmpty());
        $this->assertCount(0, $this->cartService->getItems());
        $this->assertSame(0.0, $this->cartService->subtotal());
        $this->assertSame(0.0, $this->cartService->shipping());
        $this->assertSame(0.0, $this->cartService->total());
        $this->assertSame(0, $this->cartService->count());
    }

    public function test_computes_item_totals_subtotal_and_total(): void
    {
        $phoneCategory = Category::factory()->create(['slug' => 'smartphones']);
        $product = Product::factory()->create(['category_id' => $phoneCategory->id, 'is_visible' => true]);

        $condition1 = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => GradeEnum::NEW,
            'price' => 100000,
            'quantity_in_stock' => 5,
        ]);

        $condition2 = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'grade' => GradeEnum::A,
            'price' => 80000,
            'quantity_in_stock' => 3,
        ]);

        $sessionCart = [
            $condition1->id => ['quantity' => 2],
            $condition2->id => ['quantity' => 1],
        ];

        session(['cart' => $sessionCart]);

        $items = $this->cartService->getItems();
        $this->assertCount(2, $items);

        $this->assertSame(200000.0, $items[0]->lineTotal);
        $this->assertSame(80000.0, $items[1]->lineTotal);

        // Subtotal: 200,000 + 80,000 = 280,000
        $this->assertSame(280000.0, $this->cartService->subtotal());
        // Phones get free shipping
        $this->assertSame(0.0, $this->cartService->shipping());
        $this->assertSame(280000.0, $this->cartService->total());
        $this->assertSame(3, $this->cartService->count());
        $this->assertFalse($this->cartService->isEmpty());
    }

    public function test_excludes_invisible_products(): void
    {
        $hiddenProduct = Product::factory()->create(['is_visible' => false]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $hiddenProduct->id,
            'price' => 50000,
            'quantity_in_stock' => 10,
        ]);

        session(['cart' => [$condition->id => ['quantity' => 1]]]);

        $this->assertTrue($this->cartService->isEmpty());
        $this->assertCount(0, $this->cartService->getItems());
        $this->assertSame(0.0, $this->cartService->subtotal());
    }

    public function test_clamps_quantity_to_available_stock(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 10000,
            'quantity_in_stock' => 3,
        ]);

        // User requested 10, but only 3 in stock
        session(['cart' => [$condition->id => ['quantity' => 10]]]);

        $items = $this->cartService->getItems();
        $this->assertCount(1, $items);
        $this->assertSame(3, $items[0]->quantity);
        $this->assertSame(30000.0, $items[0]->lineTotal);
    }

    public function test_cart_item_data_array_access(): void
    {
        $product = Product::factory()->create(['is_visible' => true]);
        $condition = ProductCondition::factory()->create([
            'product_id' => $product->id,
            'price' => 25000,
            'quantity_in_stock' => 5,
        ]);

        session(['cart' => [$condition->id => ['quantity' => 2]]]);

        $items = $this->cartService->getItems();
        $first = $items->first();

        $this->assertNotNull($first);
        $this->assertSame(2, $first['quantity']);
        $this->assertSame(50000.0, $first['line_total']);
        $this->assertSame($condition->id, $first['condition']->id);
    }
}
