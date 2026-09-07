<?php

namespace Database\Factories;

use App\Models\Sale;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sale>
 */
class SaleFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'date' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'customer_name' => fake()->name(),
            'items_sold' => fake()->randomElement(['Bamboo Poles', 'Bamboo Matting', 'Bamboo Baskets', 'Bamboo Furniture']),
            'quantity' => fake()->numberBetween(1, 100),
            'unit_price' => fake()->randomFloat(2, 10, 2000),
            'payment_status' => fake()->randomElement(['paid', 'partial', 'unpaid']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
