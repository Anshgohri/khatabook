<?php

namespace Database\Factories;

use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SupplierPayment>
 */
class SupplierPaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'supplier_id' => Supplier::factory(),
            'user_id' => User::factory(),
            'date' => fake()->date(),
            'type' => fake()->randomElement(['raw_material_purchase', 'payment_made']),
            'amount' => fake()->randomElement([1000.00, 5000.00, 20000.00, 50000.00]),
            'payment_method' => fake()->randomElement(['cash', 'upi', 'bank_transfer']),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
