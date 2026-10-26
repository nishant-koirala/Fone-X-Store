<?php

namespace Tests\Feature\Cart;

use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_view_empty_cart(): void
    {
        $response = $this->get(route('cart.index'));

        $response->assertStatus(200);
        $response->assertViewIs('cart.index');
        $response->assertSee('Your cart is empty');
        $this->assertEquals(0, $response->viewData('subtotal'));
        $this->assertEmpty($response->viewData('cartItems'));
    }

    public function test_can_add_item_to_cart_and_persists_in_session(): void
    {
        $condition = ProductCondition::factory()->create([
            'price' => 50000,
            'quantity_in_stock' => 5,
        ]);

        $response = $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 2,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $cart = session('cart');
        $this->assertIsArray($cart);
        $this->assertArrayHasKey($condition->id, $cart);
        $this->assertEquals(2, $cart[$condition->id]['quantity']);

        // View cart page should display item and correct line total
        $viewResponse = $this->get(route('cart.index'));
        $viewResponse->assertStatus(200);
        $viewResponse->assertSee('100,000'); // 50000 * 2
        $this->assertEquals(100000, $viewResponse->viewData('subtotal'));
    }

    public function test_adding_existing_cart_item_increments_its_quantity(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 10,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 2]]);

        $response = $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 3,
        ]);

        $response->assertRedirect(route('cart.index'));
        $cart = session('cart');
        $this->assertEquals(5, $cart[$condition->id]['quantity']);
    }

    public function test_cannot_add_more_than_available_stock(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 3,
        ]);

        $response = $this->from(route('cart.index'))->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 5,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $cart = session('cart', []);
        $this->assertArrayNotHasKey($condition->id, $cart);
    }

    public function test_cannot_add_more_when_cart_quantity_reaches_stock_limit(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 4,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 3]]);

        $response = $this->from(route('cart.index'))->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 2, // 3 + 2 = 5 > 4
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $cart = session('cart');
        $this->assertEquals(3, $cart[$condition->id]['quantity']);
    }

    public function test_cannot_add_out_of_stock_condition(): void
    {
        $condition = ProductCondition::factory()->outOfStock()->create();

        $response = $this->from(route('cart.index'))->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 1,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');
    }

    public function test_cannot_add_nonexistent_condition(): void
    {
        $response = $this->post(route('cart.add'), [
            'product_condition_id' => 999999,
            'quantity' => 1,
        ]);

        $response->assertSessionHasErrors(['product_condition_id']);
    }

    public function test_can_update_cart_item_quantity(): void
    {
        $condition = ProductCondition::factory()->create([
            'price' => 30000,
            'quantity_in_stock' => 10,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 1]]);

        $response = $this->patch(route('cart.update', $condition->id), [
            'quantity' => 4,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $cart = session('cart');
        $this->assertEquals(4, $cart[$condition->id]['quantity']);
    }

    public function test_cannot_update_quantity_beyond_available_stock(): void
    {
        $condition = ProductCondition::factory()->create([
            'quantity_in_stock' => 3,
        ]);

        $this->setCartSession([$condition->id => ['quantity' => 1]]);

        $response = $this->from(route('cart.index'))->patch(route('cart.update', $condition->id), [
            'quantity' => 10,
        ]);

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('error');

        $cart = session('cart');
        $this->assertEquals(1, $cart[$condition->id]['quantity']);
    }

    public function test_can_remove_item_from_cart(): void
    {
        $condition1 = ProductCondition::factory()->create(['price' => 20000, 'quantity_in_stock' => 5]);
        $condition2 = ProductCondition::factory()->create(['price' => 30000, 'quantity_in_stock' => 5]);

        $this->setCartSession([
            $condition1->id => ['quantity' => 1],
            $condition2->id => ['quantity' => 2],
        ]);

        $response = $this->delete(route('cart.destroy', $condition1->id));

        $response->assertRedirect(route('cart.index'));
        $response->assertSessionHas('success');

        $cart = session('cart');
        $this->assertArrayNotHasKey($condition1->id, $cart);
        $this->assertArrayHasKey($condition2->id, $cart);
    }

    public function test_totals_computed_correctly_with_multiple_conditions(): void
    {
        $cond1 = ProductCondition::factory()->create(['price' => 45000, 'quantity_in_stock' => 10]);
        $cond2 = ProductCondition::factory()->create(['price' => 65000, 'quantity_in_stock' => 10]);

        $this->setCartSession([
            $cond1->id => ['quantity' => 2], // 90,000
            $cond2->id => ['quantity' => 1], // 65,000
        ]);

        $response = $this->get(route('cart.index'));
        $response->assertStatus(200);

        // Expected subtotal: 90000 + 65000 = 155000
        $this->assertEquals(155000, $response->viewData('subtotal'));
        $this->assertCount(2, $response->viewData('cartItems'));
    }

    public function test_quantity_edge_cases_zero_negative_non_numeric_and_huge_numbers(): void
    {
        $condition = ProductCondition::factory()->create(['quantity_in_stock' => 5]);

        // Zero quantity rejected
        $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 0,
        ])->assertSessionHasErrors(['quantity']);

        // Negative quantity rejected
        $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => -3,
        ])->assertSessionHasErrors(['quantity']);

        // Non-numeric quantity rejected
        $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 'abc',
        ])->assertSessionHasErrors(['quantity']);

        // Huge number rejected by stock check
        $this->post(route('cart.add'), [
            'product_condition_id' => $condition->id,
            'quantity' => 9999999,
        ])->assertSessionHas('error');
    }
}
