<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SamsungCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $samsungCategory = Category::firstOrCreate(
            ['slug' => 'samsung'],
            ['name' => 'Samsung']
        );

        $flagships = [
            ['name' => 'Samsung Galaxy S24 Ultra', 'base_price' => 1400, 'featured' => true],
            ['name' => 'Samsung Galaxy S24 Plus', 'base_price' => 1050, 'featured' => false],
            ['name' => 'Samsung Galaxy S24', 'base_price' => 850, 'featured' => true],
            ['name' => 'Samsung Galaxy S23 Ultra', 'base_price' => 1100, 'featured' => false],
            ['name' => 'Samsung Galaxy S23', 'base_price' => 700, 'featured' => false],
            ['name' => 'Samsung Galaxy Z Fold 6', 'base_price' => 1700, 'featured' => true],
            ['name' => 'Samsung Galaxy Z Flip 6', 'base_price' => 1000, 'featured' => false],
            ['name' => 'Samsung Galaxy A55 5G', 'base_price' => 450, 'featured' => false],
            ['name' => 'Samsung Galaxy A35 5G', 'base_price' => 350, 'featured' => false],
        ];

        foreach ($flagships as $model) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($model['name'])],
                [
                    'name' => $model['name'],
                    'brand' => 'Samsung',
                    'category_id' => $samsungCategory->id,
                    'base_price' => $model['base_price'],
                    'description' => 'The '.$model['name'].' delivers premium performance and brilliant display technology.',
                    'is_visible' => true,
                    'is_featured' => $model['featured'],
                    'is_new' => false,
                ]
            );

            $conditions = [
                ['grade' => 'new', 'modifier' => 1.0, 'qty' => 8],
                ['grade' => 'A', 'modifier' => 0.85, 'qty' => 6],
                ['grade' => 'B', 'modifier' => 0.70, 'qty' => 4],
            ];

            foreach ($conditions as $cond) {
                $price = round($model['base_price'] * $cond['modifier'], 2);

                $product->conditions()->firstOrCreate(
                    ['grade' => $cond['grade']],
                    [
                        'price' => $price,
                        'original_price' => $price,
                        'quantity_in_stock' => $cond['qty'],
                    ]
                );
            }
        }
    }
}
