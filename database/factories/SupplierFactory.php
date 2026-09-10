<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Supplier>
 */
class SupplierFactory extends Factory
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
            'name' => fake()->name().' Traders',
            'phone' => fake()->phoneNumber(),
            'location' => fake()->city(),
            'material_supplied' => fake()->randomElement(['Bans (Bamboo)', 'Fatta (Wood Planks)', 'Steel Wire', 'Plywood']),
            'outstanding_balance' => 0.00,
            'status' => 'active',
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
