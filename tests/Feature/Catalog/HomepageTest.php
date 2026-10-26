<?php

namespace Tests\Feature\Catalog;

use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomepageTest extends TestCase
{
    use RefreshDatabase;

    public function test_homepage_loads_successfully_without_authentication(): void
    {
        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertViewIs('home');
        $response->assertViewHasAll(['featuredConditions', 'accessoryCategories', 'heroConditions']);
    }

    public function test_homepage_displays_in_stock_products_from_database(): void
    {
        $product = $this->createProductWithConditions(['name' => 'Featured Phone Pro']);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Featured Phone Pro');
    }

    public function test_homepage_hides_invisible_products(): void
    {
        $product = $this->createProductWithConditions([
            'name' => 'Hidden Secret Device',
            'is_visible' => false,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Hidden Secret Device');
    }

    public function test_homepage_hides_completely_out_of_stock_conditions(): void
    {
        $product = Product::factory()->create(['name' => 'Empty Stock Mobile', 'is_visible' => true]);
        ProductCondition::factory()->create([
            'product_id' => $product->id,
            'quantity_in_stock' => 0,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertDontSee('Empty Stock Mobile');
    }

    public function test_homepage_shows_featured_phone_in_hero_slider(): void
    {
        $product = $this->createProductWithConditions([
            'name' => 'Ultra Star Flagship',
            'is_featured' => true,
            'is_visible' => true,
        ]);

        $response = $this->get(route('home'));

        $response->assertStatus(200);
        $response->assertSee('Ultra Star Flagship');
    }
}
