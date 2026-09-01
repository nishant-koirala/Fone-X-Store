<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class IphoneCatalogSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Ensure Apple category exists
        $appleCategory = Category::firstOrCreate(
            ['slug' => 'apple'],
            ['name' => 'Apple']
        );

        $flagships = [
            ['name' => 'iPhone 7', 'base_price' => 150],
            ['name' => 'iPhone 7 Plus', 'base_price' => 200],
            ['name' => 'iPhone 8', 'base_price' => 220],
            ['name' => 'iPhone 8 Plus', 'base_price' => 270],
            ['name' => 'iPhone X', 'base_price' => 300],
            ['name' => 'iPhone XR', 'base_price' => 320],
            ['name' => 'iPhone XS', 'base_price' => 350],
            ['name' => 'iPhone XS Max', 'base_price' => 400],
            
            ['name' => 'iPhone 11', 'base_price' => 450],
            ['name' => 'iPhone 11 Pro', 'base_price' => 520],
            ['name' => 'iPhone 11 Pro Max', 'base_price' => 580],

            ['name' => 'iPhone 12 Mini', 'base_price' => 500],
            ['name' => 'iPhone 12', 'base_price' => 550],
            ['name' => 'iPhone 12 Pro', 'base_price' => 650],
            ['name' => 'iPhone 12 Pro Max', 'base_price' => 750],

            ['name' => 'iPhone 13 Mini', 'base_price' => 600],
            ['name' => 'iPhone 13', 'base_price' => 680],
            ['name' => 'iPhone 13 Pro', 'base_price' => 800],
            ['name' => 'iPhone 13 Pro Max', 'base_price' => 900],

            ['name' => 'iPhone 14', 'base_price' => 750],
            ['name' => 'iPhone 14 Plus', 'base_price' => 850],
            ['name' => 'iPhone 14 Pro', 'base_price' => 950],
            ['name' => 'iPhone 14 Pro Max', 'base_price' => 1050],

            ['name' => 'iPhone 15', 'base_price' => 850],
            ['name' => 'iPhone 15 Plus', 'base_price' => 950],
            ['name' => 'iPhone 15 Pro', 'base_price' => 1050],
            ['name' => 'iPhone 15 Pro Max', 'base_price' => 1150],
            
            ['name' => 'iPhone 16', 'base_price' => 900],
            ['name' => 'iPhone 16 Plus', 'base_price' => 1000],
            ['name' => 'iPhone 16 Pro', 'base_price' => 1100],
            ['name' => 'iPhone 16 Pro Max', 'base_price' => 1200],

            ['name' => 'iPhone 17', 'base_price' => 1000],
            ['name' => 'iPhone 17 Plus', 'base_price' => 1100],
            ['name' => 'iPhone 17 Pro', 'base_price' => 1200],
            ['name' => 'iPhone 17 Pro Max', 'base_price' => 1300],
        ];

        foreach ($flagships as $model) {
            $product = Product::firstOrCreate(
                ['slug' => Str::slug($model['name'])],
                [
                    'name' => $model['name'],
                    'brand' => 'Apple',
                    'category_id' => $appleCategory->id,
                    'base_price' => $model['base_price'],
                    'description' => 'The ' . $model['name'] . ' offers an incredible experience. This is a placeholder description that you can edit later in the admin panel.',
                    'is_visible' => true,
                    'is_new' => false,
                ]
            );

            // Create conditions if they don't exist
            $conditions = [
                ['grade' => 'new', 'modifier' => 1.0, 'qty' => 10],
                ['grade' => 'A', 'modifier' => 0.85, 'qty' => 5],
                ['grade' => 'B', 'modifier' => 0.70, 'qty' => 3],
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
