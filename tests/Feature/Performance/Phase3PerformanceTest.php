<?php

namespace Tests\Feature\Performance;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\HasMedia;
use Tests\Support\InteractsWithShop;
use Tests\TestCase;

class Phase3PerformanceTest extends TestCase
{
    use InteractsWithShop;
    use RefreshDatabase;

    public function test_product_query_scopes(): void
    {
        $category = Category::factory()->create();

        $visibleProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_visible' => true,
        ]);
        $hiddenProduct = Product::factory()->create([
            'category_id' => $category->id,
            'is_visible' => false,
        ]);

        ProductCondition::factory()->create([
            'product_id' => $visibleProduct->id,
            'quantity_in_stock' => 5,
        ]);
        ProductCondition::factory()->create([
            'product_id' => $hiddenProduct->id,
            'quantity_in_stock' => 0,
        ]);

        $visibleResults = Product::visible()->pluck('id');
        $this->assertTrue($visibleResults->contains($visibleProduct->id));
        $this->assertFalse($visibleResults->contains($hiddenProduct->id));

        $inStockResults = Product::inStock()->pluck('id');
        $this->assertTrue($inStockResults->contains($visibleProduct->id));
        $this->assertFalse($inStockResults->contains($hiddenProduct->id));
    }

    public function test_product_condition_query_scopes(): void
    {
        $category = Category::factory()->create();

        $visibleProduct = Product::factory()->create(['category_id' => $category->id, 'is_visible' => true]);
        $hiddenProduct = Product::factory()->create(['category_id' => $category->id, 'is_visible' => false]);

        $inStockVisible = ProductCondition::factory()->create(['product_id' => $visibleProduct->id, 'quantity_in_stock' => 4]);
        $outOfStockVisible = ProductCondition::factory()->create(['product_id' => $visibleProduct->id, 'quantity_in_stock' => 0]);
        $inStockHidden = ProductCondition::factory()->create(['product_id' => $hiddenProduct->id, 'quantity_in_stock' => 3]);

        $inStockIds = ProductCondition::inStock()->pluck('id');
        $this->assertTrue($inStockIds->contains($inStockVisible->id));
        $this->assertTrue($inStockIds->contains($inStockHidden->id));
        $this->assertFalse($inStockIds->contains($outOfStockVisible->id));

        $visibleProductConditionIds = ProductCondition::visibleProduct()->pluck('id');
        $this->assertTrue($visibleProductConditionIds->contains($inStockVisible->id));
        $this->assertTrue($visibleProductConditionIds->contains($outOfStockVisible->id));
        $this->assertFalse($visibleProductConditionIds->contains($inStockHidden->id));
    }

    public function test_product_implements_has_media_and_generates_thumb_conversion(): void
    {
        Storage::fake('public');

        $this->assertTrue(is_subclass_of(Product::class, HasMedia::class));

        $product = $this->createProductWithConditions();
        $fakeImage = UploadedFile::fake()->image('test-phone.jpg', 800, 800);

        $media = $product->addMedia($fakeImage)
            ->toMediaCollection('images');

        $this->assertNotNull($media);
        $this->assertEquals('images', $media->collection_name);
        $this->assertTrue($product->hasMedia('images'));
        $this->assertNotEmpty($product->getFirstMediaUrl('images', 'thumb'));
    }

    public function test_convert_product_images_artisan_command(): void
    {
        Storage::fake('public');

        $fakeFile = UploadedFile::fake()->image('sample.jpg', 600, 600);
        $storedPath = $fakeFile->store('products', 'public');

        $product = $this->createProductWithConditions(['image' => $storedPath]);
        $this->assertFalse($product->hasMedia('images'));

        $this->artisan('products:convert-images')
            ->expectsOutputToContain('Completed image conversion: 1 converted, 0 skipped.')
            ->assertExitCode(0);

        $this->assertTrue($product->fresh()->hasMedia('images'));
    }

    public function test_accessory_category_slugs_loaded_from_config(): void
    {
        $slugs = config('shop.accessory_category_slugs');
        $this->assertIsArray($slugs);
        $this->assertContains('accessories', $slugs);
        $this->assertContains('chargers-cables', $slugs);

        // Test homepage loads without error using configured slugs
        $response = $this->get('/');
        $response->assertOk();
    }

    public function test_performance_database_indexes_exist(): void
    {
        $this->assertTrue(Schema::hasIndex('products', ['is_visible']));
        $this->assertTrue(Schema::hasIndex('products', ['is_featured']));
        $this->assertTrue(Schema::hasIndex('orders', ['status']));
        $this->assertTrue(Schema::hasIndex('product_conditions', ['quantity_in_stock']));
        $this->assertTrue(Schema::hasIndex('trade_in_valuations', ['status']));
    }
}
