<?php

namespace Database\Factories;

use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinancierPayment>
 */
class FinancierPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'financier_id' => Financier::factory(),
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'type' => fake()->randomElement(['daily_payment', 'monthly_payment', 'loan_received', 'loan_repaid', 'interest_payment']),
            'amount' => fake()->randomElement([500.00, 1000.00, 5000.00, 50000.00]),
            'payment_method' => fake()->randomElement(['cash', 'upi', 'bank_transfer']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
