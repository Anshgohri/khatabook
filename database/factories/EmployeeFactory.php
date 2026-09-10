<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
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
            'employee_user_id' => null,
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'default_daily_rate' => fake()->randomElement([500.00, 800.00, 1000.00]),
            'advance_balance' => 0.00,
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
