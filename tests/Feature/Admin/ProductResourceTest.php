<?php

namespace Tests\Feature\Admin;

use App\Filament\Resources\ProductResource\Pages\CreateProduct;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\Pages\ListProducts;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProductResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->loginAsAdmin();
    }

    public function test_can_list_products_in_admin(): void
    {
        $products = Product::factory()->count(2)->create();

        Livewire::test(ListProducts::class)
            ->assertSuccessful()
            ->assertCanSeeTableRecords($products);
    }

    public function test_can_create_product_with_conditions_in_admin(): void
    {
        $category = Category::factory()->create();

        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => 'Pixel 9 Pro Fold',
                'slug' => 'pixel-9-pro-fold',
                'brand' => 'Google',
                'category_id' => $category->id,
                'base_price' => 220000,
                'is_new' => true,
                'is_visible' => true,
                'conditions' => [
                    [
                        'grade' => 'new',
                        'price' => 220000,
                        'original_price' => 240000,
                        'quantity_in_stock' => 5,
                    ],
                ],
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('products', [
            'name' => 'Pixel 9 Pro Fold',
            'slug' => 'pixel-9-pro-fold',
            'brand' => 'Google',
        ]);

        $this->assertDatabaseHas('product_conditions', [
            'grade' => 'new',
            'price' => 220000,
            'quantity_in_stock' => 0,
        ]);
    }

    public function test_product_validation_errors_surface(): void
    {
        Livewire::test(CreateProduct::class)
            ->fillForm([
                'name' => null,
                'slug' => null,
                'brand' => null,
                'category_id' => null,
            ])
            ->call('create')
            ->assertHasFormErrors(['name', 'slug', 'brand', 'category_id']);
    }

    public function test_can_edit_product_in_admin(): void
    {
        $product = Product::factory()->create(['name' => 'Original Name']);

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->fillForm([
                'name' => 'Updated Admin Name',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEquals('Updated Admin Name', $product->fresh()->name);
    }

    public function test_can_delete_product_in_admin(): void
    {
        $product = Product::factory()->create();

        Livewire::test(EditProduct::class, ['record' => $product->getRouteKey()])
            ->callAction('delete');

        $this->assertSoftDeleted($product);
    }
}
