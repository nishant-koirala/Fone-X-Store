<?php

namespace Database\Factories;

use App\Enums\DeviceUnitStatusEnum;
use App\Models\DeviceUnit;
use App\Models\ProductCondition;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceUnit>
 */
class DeviceUnitFactory extends Factory
{
    protected $model = DeviceUnit::class;

    public function definition(): array
    {
        return [
            'product_condition_id' => ProductCondition::factory(),
            'imei' => fake()->numerify('35693803######'),
            'serial_number' => fake()->bothify('F2LZG####???'),
            'battery_health' => fake()->numberBetween(80, 100),
            'cost_price' => fake()->numberBetween(25000, 90000),
            'status' => DeviceUnitStatusEnum::AVAILABLE,
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function sold(): static
    {
        return $this->state(fn () => [
            'status' => DeviceUnitStatusEnum::SOLD,
            'sold_at' => now(),
        ]);
    }

    public function available(): static
    {
        return $this->state(fn () => [
            'status' => DeviceUnitStatusEnum::AVAILABLE,
        ]);
    }
}
