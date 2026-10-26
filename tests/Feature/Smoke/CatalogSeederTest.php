<?php

namespace Tests\Feature\Smoke;

use App\Models\Category;
use App\Models\Product;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\IphoneCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_runs_cleanly_and_respects_catalog_constraints(): void
    {
        $this->seed(DatabaseSeeder::class);

        // Verify categories seeded
        $this->assertDatabaseHas('categories', [
            'slug' => 'apple',
            'name' => 'Apple',
        ]);

        $appleCategory = Category::where('slug', 'apple')->first();
        $this->assertNotNull($appleCategory);

        // Verify products seeded
        $productCount = Product::count();
        $this->assertGreaterThan(0, $productCount, 'Seeder should insert products');
        $this->assertLessThanOrEqual(150, $productCount, 'Total catalog must not exceed 150 products');

        // Verify every product has at least one valid condition with stock and positive price
        $products = Product::with('conditions')->get();
        foreach ($products as $product) {
            $this->assertGreaterThan(0, $product->conditions->count(), "Product [{$product->name}] must have conditions");
            
            foreach ($product->conditions as $cond) {
                $this->assertGreaterThan(0, (float) $cond->price, "Condition price for [{$product->name} - {$cond->grade}] must be positive");
                $this->assertGreaterThanOrEqual(0, $cond->quantity_in_stock, "Stock for [{$product->name} - {$cond->grade}] cannot be negative");
                $this->assertContains($cond->grade, ['new', 'A', 'B', 'C'], "Condition grade for [{$product->name}] is valid");
            }
        }
    }

    public function test_seeded_catalog_is_browsable_and_filterable_via_storefront(): void
    {
        $this->seed(IphoneCatalogSeeder::class);

        // Test product catalog index
        $indexResponse = $this->get(route('products.index'));
        $indexResponse->assertOk();
        $indexResponse->assertSee('iPhone');

        // Test search filter
        $searchResponse = $this->get(route('products.index', ['search' => 'iPhone 15']));
        $searchResponse->assertOk();
        $searchResponse->assertSee('iPhone 15');

        $searchSeven = $this->get(route('products.index', ['search' => 'iPhone 7']));
        $searchSeven->assertOk();
        $searchSeven->assertSee('iPhone 7');

        // Test category filter
        $filterResponse = $this->get(route('products.index', ['category' => 'apple']));
        $filterResponse->assertOk();
        $filterResponse->assertSee('iPhone');

        // Test first product show page
        $firstProduct = Product::first();
        $this->assertNotNull($firstProduct);

        $showResponse = $this->get(route('products.show', $firstProduct->slug));
        $showResponse->assertOk();
        $showResponse->assertSee($firstProduct->name);
    }
}
