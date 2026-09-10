<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<EmployeePayment>
 */
class EmployeePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'employee_id' => Employee::factory(),
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'type' => fake()->randomElement(['daily_pay', 'advance_given', 'advance_repaid', 'salary_deduction']),
            'amount' => fake()->randomElement([500.00, 800.00, 1000.00, 100000.00]),
            'payment_method' => fake()->randomElement(['cash', 'upi', 'bank_transfer']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
