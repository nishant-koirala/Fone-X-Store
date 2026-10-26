<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
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

    public function test_can_view_empty_cart()
    {
        $response = $this->get(route('cart.index'));
        $response->assertStatus(200);
        $response->assertSee('Shopping Cart');
    }

    public function test_can_add_item_to_cart()
    {
        $response = $this->post(route('cart.add'), [
            'product_condition_id' => $this->condition->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $cart = session('cart');
        $this->assertNotNull($cart);
        $this->assertArrayHasKey($this->condition->id, $cart);
        $this->assertEquals(1, $cart[$this->condition->id]['quantity']);
    }

    public function test_cannot_add_more_items_than_stock()
    {
        $response = $this->post(route('cart.add'), [
            'product_condition_id' => $this->condition->id,
            'quantity' => 10,
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
