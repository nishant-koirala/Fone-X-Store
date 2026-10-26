<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\TradeInValuation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TradeInValuation>
 */
class TradeInValuationFactory extends Factory
{
    protected $model = TradeInValuation::class;

    public function definition(): array
    {
        $brands = ['Apple' => 'iPhone 12', 'Samsung' => 'Galaxy S21', 'Xiaomi' => 'Redmi Note 11', 'OnePlus' => 'OnePlus 9'];
        $brand = fake()->randomElement(array_keys($brands));
        $model = $brands[$brand];

        return [
            'customer_id' => Customer::factory(),
            'device_brand' => $brand,
            'device_model' => $model,
            'condition_description' => 'Overall Condition: Good | Notes: Screen clean, minor body wear',
            'estimated_value' => null,
            'status' => 'pending',
        ];
    }

    public function pending(): static
    {
        return $this->state(fn () => [
            'status' => 'pending',
            'estimated_value' => null,
        ]);
    }

    public function reviewed(?float $value = 45000.00): static
    {
        return $this->state(fn () => [
            'status' => 'reviewed',
            'estimated_value' => $value,
        ]);
    }

    public function completed(?float $value = 45000.00): static
    {
        return $this->state(fn () => [
            'status' => 'completed',
            'estimated_value' => $value,
        ]);
    }
}
