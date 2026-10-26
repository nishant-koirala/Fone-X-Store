<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\Review;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Review>
 */
class ReviewFactory extends Factory
{
    protected $model = Review::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'name' => fake()->name(),
            'rating' => fake()->numberBetween(3, 5),
            'comment' => fake()->sentence(12),
            'is_approved' => false,
        ];
    }

    public function approved(): static
    {
        return $this->state(fn () => ['is_approved' => true]);
    }
}
