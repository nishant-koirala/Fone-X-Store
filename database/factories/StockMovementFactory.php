<?php

namespace Database\Factories;

use App\Models\ProductCondition;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        return [
            'product_condition_id' => ProductCondition::factory(),
            'type' => 'restock',
            'quantity' => fake()->numberBetween(1, 15),
            'note' => fake()->sentence(),
            'created_by' => User::factory(),
        ];
    }

    public function sale(int $qty = 1): static
    {
        return $this->state(fn () => [
            'type' => 'sale',
            'quantity' => -abs($qty),
            'note' => 'Customer sale order',
        ]);
    }

    public function restock(int $qty = 10): static
    {
        return $this->state(fn () => [
            'type' => 'restock',
            'quantity' => abs($qty),
            'note' => 'Supplier inventory delivery',
        ]);
    }

    public function exchangeIn(int $qty = 1): static
    {
        return $this->state(fn () => [
            'type' => 'exchange_in',
            'quantity' => abs($qty),
            'note' => 'Customer trade-in accepted into inventory',
        ]);
    }

    public function adjustment(int $qty = 1): static
    {
        return $this->state(fn () => [
            'type' => 'adjustment',
            'quantity' => $qty,
            'note' => 'Inventory audit discrepancy adjustment',
        ]);
    }
}
