<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $brands = [
            'Apple' => ['iPhone 13', 'iPhone 14 Pro', 'iPhone 15', 'iPhone 15 Pro Max', 'iPhone 16 Pro'],
            'Samsung' => ['Galaxy S23 Ultra', 'Galaxy S24', 'Galaxy S24 Ultra', 'Galaxy Z Fold 5', 'Galaxy A55'],
            'Xiaomi' => ['Xiaomi 14', 'Redmi Note 13 Pro', 'POCO F6 Pro', 'Xiaomi 13T'],
            'Google' => ['Pixel 8', 'Pixel 8 Pro', 'Pixel 9 Pro XL'],
        ];

        $brand = fake()->randomElement(array_keys($brands));
        $modelName = fake()->randomElement($brands[$brand]);
        $uniqueSuffix = fake()->unique()->numberBetween(100, 99999);
        $name = "{$modelName} {$uniqueSuffix}";

        // Realistic NPR pricing: Rs 30,000 to Rs 250,000
        $basePrice = fake()->numberBetween(350, 2400) * 100;

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'brand' => $brand,
            'image' => null,
            'description' => fake()->paragraph(),
            'category_id' => Category::factory(),
            'base_price' => $basePrice,
            'is_new' => fake()->boolean(70),
            'is_visible' => true,
            'is_featured' => fake()->boolean(20),
            'gallery' => [],
        ];
    }

    public function hidden(): static
    {
        return $this->state(fn () => ['is_visible' => false]);
    }

    public function featured(): static
    {
        return $this->state(fn () => ['is_featured' => true, 'is_visible' => true]);
    }
}
