<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductCondition>
 */
class ProductConditionFactory extends Factory
{
    protected $model = ProductCondition::class;

    public function definition(): array
    {
        $grade = fake()->randomElement(['new', 'A', 'B', 'C']);
        $modifier = match ($grade) {
            'new' => 1.00,
            'A' => 0.85,
            'B' => 0.70,
            'C' => 0.55,
            default => 1.00,
        };

        // Realistic NPR price
        $base = fake()->numberBetween(400, 2200) * 100;
        $price = round($base * $modifier, 2);
        $originalPrice = round($price * 1.10, 2);

        return [
            'product_id' => Product::factory(),
            'grade' => $grade,
            'price' => $price,
            'original_price' => $originalPrice,
            'quantity_in_stock' => fake()->numberBetween(3, 20),
        ];
    }

    public function grade(string $grade): static
    {
        return $this->state(fn () => ['grade' => $grade]);
    }

    public function inStock(int $qty = 10): static
    {
        return $this->state(fn () => ['quantity_in_stock' => $qty]);
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['quantity_in_stock' => 0]);
    }

    public function lowStock(int $qty = 2): static
    {
        return $this->state(fn () => ['quantity_in_stock' => $qty]);
    }
}
