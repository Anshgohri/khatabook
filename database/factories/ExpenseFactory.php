<?php

namespace Database\Factories;

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Expense>
 */
class ExpenseFactory extends Factory
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
            'expense_category_id' => ExpenseCategory::factory(),
            'date' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'description' => fake()->sentence(),
            'amount' => fake()->randomFloat(2, 10, 3000),
            'payment_method' => fake()->randomElement(['cash', 'bank_transfer', 'upi', 'cheque', 'other']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
