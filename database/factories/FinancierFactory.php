<?php

namespace Database\Factories;

use App\Models\Financier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Financier>
 */
class FinancierFactory extends Factory
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
            'financier_user_id' => null,
            'name' => fake()->company().' Finance',
            'phone' => fake()->phoneNumber(),
            'payout_type' => fake()->randomElement(['daily', 'monthly']),
            'default_payment_amount' => fake()->randomElement([500.00, 1000.00, 5000.00]),
            'outstanding_balance' => 0.00,
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
