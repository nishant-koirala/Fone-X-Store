<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            'Apple iPhones',
            'Samsung Galaxy',
            'Xiaomi & Redmi',
            'OnePlus Flagships',
            'Google Pixel',
            'Accessories',
            'Chargers & Cables',
            'Cases & Covers',
            'Audio & Speakers',
            'Power Banks',
            'Smart Watches',
            'Tablets & iPads',
        ]);

        return [
            'name' => $name,
            'slug' => Str::slug($name) . '-' . fake()->unique()->numberBetween(100, 9999),
        ];
    }
}
