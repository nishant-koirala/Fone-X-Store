<?php

namespace Tests\Feature\Catalog;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIndexTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_loads_successfully(): void
    {
        $response = $this->get(route('products.index'));

        $response->assertStatus(200);
        $response->assertViewIs('products.index');
        $response->assertViewHasAll(['conditions', 'categories', 'brands']);
    }

    public function test_catalog_paginates_products_at_twelve_per_page(): void
    {
        $category = Category::factory()->create(['slug' => 'smartphones']);

        for ($i = 1; $i <= 15; $i++) {
            $product = Product::factory()->create([
                'name' => "Catalog Device {$i}",
                'category_id' => $category->id,
                'is_visible' => true,
            ]);
            ProductCondition::factory()->create([
                'product_id' => $product->id,
                'quantity_in_stock' => 5,
            ]);
        }

        $response = $this->get(route('products.index'));
        $response->assertStatus(200);

        $conditions = $response->viewData('conditions');
        $this->assertEquals(12, $conditions->count());
        $this->assertEquals(15, $conditions->total());
        $this->assertEquals(2, $conditions->lastPage());
    }

    public function test_filter_by_category(): void
    {
        $appleCat = Category::factory()->create(['name' => 'Apple', 'slug' => 'apple']);
        $samsungCat = Category::factory()->create(['name' => 'Samsung', 'slug' => 'samsung']);

        $applePhone = $this->createProductWithConditions(['name' => 'iPhone 15 Pro', 'category_id' => $appleCat->id]);
        $samsungPhone = $this->createProductWithConditions(['name' => 'Galaxy S24', 'category_id' => $samsungCat->id]);

        $response = $this->get(route('products.index', ['category' => 'apple']));

        $response->assertStatus(200);
        $response->assertSee('iPhone 15 Pro');
        $response->assertDontSee('Galaxy S24');
    }

    public function test_filter_by_brand(): void
    {
        $cat = Category::factory()->create(['slug' => 'smartphones']);
        $apple = $this->createProductWithConditions(['name' => 'iPhone 13', 'brand' => 'Apple', 'category_id' => $cat->id]);
        $xiaomi = $this->createProductWithConditions(['name' => 'Xiaomi 14', 'brand' => 'Xiaomi', 'category_id' => $cat->id]);

        $response = $this->get(route('products.index', ['brand' => 'Apple']));

        $response->assertStatus(200);
        $response->assertSee('iPhone 13');
        $response->assertDontSee('Xiaomi 14');
    }

    public function test_filter_by_condition_grade(): void
    {
        $cat = Category::factory()->create(['slug' => 'phones']);
        $product = Product::factory()->create(['name' => 'Grade Test Phone', 'category_id' => $cat->id, 'is_visible' => true]);

        $newCond = ProductCondition::factory()->create(['product_id' => $product->id, 'grade' => 'new', 'price' => 100000]);
        $gradeBCond = ProductCondition::factory()->create(['product_id' => $product->id, 'grade' => 'B', 'price' => 70000]);

        // Filter new only
        $response = $this->get(route('products.index', ['grade' => 'new']));
        $response->assertStatus(200);
        $items = $response->viewData('conditions');
        $this->assertTrue($items->contains('id', $newCond->id));
        $this->assertFalse($items->contains('id', $gradeBCond->id));

        // Filter used (covers A, B, C)
        $responseUsed = $this->get(route('products.index', ['grade' => 'used']));
        $responseUsed->assertStatus(200);
        $usedItems = $responseUsed->viewData('conditions');
        $this->assertTrue($usedItems->contains('id', $gradeBCond->id));
        $this->assertFalse($usedItems->contains('id', $newCond->id));
    }

    public function test_filter_by_price_range(): void
    {
        $cat = Category::factory()->create(['slug' => 'phones']);
        $p1 = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Cheap Phone']);
        $c1 = ProductCondition::factory()->create(['product_id' => $p1->id, 'price' => 25000]);

        $p2 = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Expensive Phone']);
        $c2 = ProductCondition::factory()->create(['product_id' => $p2->id, 'price' => 150000]);

        $response = $this->get(route('products.index', [
            'min_price' => 20000,
            'max_price' => 50000,
        ]));

        $response->assertStatus(200);
        $response->assertSee('Cheap Phone');
        $response->assertDontSee('Expensive Phone');
    }

    public function test_sort_by_price_ascending_and_descending(): void
    {
        $cat = Category::factory()->create(['slug' => 'phones']);
        $pLow = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Budget Phone']);
        ProductCondition::factory()->create(['product_id' => $pLow->id, 'price' => 20000]);

        $pHigh = Product::factory()->create(['category_id' => $cat->id, 'name' => 'Luxury Phone']);
        ProductCondition::factory()->create(['product_id' => $pHigh->id, 'price' => 180000]);

        // Ascending
        $responseAsc = $this->get(route('products.index', ['sort' => 'price_asc']));
        $conditionsAsc = $responseAsc->viewData('conditions');
        $this->assertEquals(20000, $conditionsAsc->first()->price);

        // Descending
        $responseDesc = $this->get(route('products.index', ['sort' => 'price_desc']));
        $conditionsDesc = $responseDesc->viewData('conditions');
        $this->assertEquals(180000, $conditionsDesc->first()->price);
    }

    public function test_combined_filters(): void
    {
        $appleCat = Category::factory()->create(['slug' => 'apple']);
        $samsungCat = Category::factory()->create(['slug' => 'samsung']);

        $targetProduct = Product::factory()->create([
            'name' => 'iPhone 15 Target',
            'brand' => 'Apple',
            'category_id' => $appleCat->id,
            'is_visible' => true,
        ]);
        $targetCond = ProductCondition::factory()->create([
            'product_id' => $targetProduct->id,
            'grade' => 'new',
            'price' => 120000,
        ]);

        $distractor = Product::factory()->create([
            'name' => 'Galaxy Target Distractor',
            'brand' => 'Samsung',
            'category_id' => $samsungCat->id,
            'is_visible' => true,
        ]);
        ProductCondition::factory()->create([
            'product_id' => $distractor->id,
            'grade' => 'new',
            'price' => 120000,
        ]);

        $response = $this->get(route('products.index', [
            'category' => 'apple',
            'brand' => 'Apple',
            'grade' => 'new',
            'min_price' => 100000,
            'max_price' => 150000,
        ]));

        $response->assertStatus(200);
        $response->assertSee('iPhone 15 Target');
        $response->assertDontSee('Galaxy Target Distractor');
    }

    public function test_empty_state_when_no_products_match(): void
    {
        $response = $this->get(route('products.index', ['search' => 'NonExistentModelX999']));

        $response->assertStatus(200);
        $this->assertCount(0, $response->viewData('conditions'));
    }

    public function test_invalid_filter_values_handled_gracefully(): void
    {
        $response = $this->get(route('products.index', [
            'category' => 'non-existent-category-slug',
            'min_price' => 'invalid_number',
            'max_price' => -999,
            'grade' => 'invalid_grade_xxx',
            'sort' => 'unknown_sort_option',
        ]));

        $response->assertStatus(200);
    }
}
