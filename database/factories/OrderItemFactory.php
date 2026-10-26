<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OrderItem>
 */
class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        return [
            'order_id' => Order::factory(),
            'product_condition_id' => ProductCondition::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'price_at_purchase' => fake()->numberBetween(350, 2000) * 100,
        ];
    }
}
