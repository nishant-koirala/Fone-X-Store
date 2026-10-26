<?php

namespace Database\Factories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Customer>
 */
class CustomerFactory extends Factory
{
    protected $model = Customer::class;

    public function definition(): array
    {
        $cities = ['Kathmandu', 'Lalitpur', 'Bhaktapur', 'Pokhara', 'Chitwan', 'Butwal', 'Biratnagar'];
        $selectedCity = fake()->randomElement($cities);

        return [
            'name' => fake()->name(),
            // Realistic Nepali mobile phone prefix (98XXXXXXXX or 97XXXXXXXX)
            'phone' => '98'.fake()->unique()->numerify('########'),
            'email' => fake()->safeEmail(),
            'address' => fake()->streetAddress().', '.$selectedCity.', Nepal',
        ];
    }
}
