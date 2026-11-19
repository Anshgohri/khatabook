<?php

namespace Database\Factories;

use App\Models\ManualRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ManualRecord>
 */
class ManualRecordFactory extends Factory
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
            'party_type' => fake()->randomElement(['supplier', 'financier', 'employee', 'other']),
            'party_name' => fake()->company().' Daily Supply',
            'party_phone' => fake()->phoneNumber(),
            'amount' => fake()->randomFloat(2, 200, 10000),
            'due_date' => fake()->dateTimeBetween('-1 week', '+1 week')->format('Y-m-d'),
            'frequency' => fake()->randomElement(['daily', 'weekly', 'monthly', 'one_time']),
            'priority' => fake()->randomElement(['low', 'normal', 'high', 'urgent']),
            'status' => 'pending',
            'paid_amount' => 0.00,
            'notes' => fake()->optional()->sentence(),
            'is_recurring' => true,
        ];
    }
}
