<?php

namespace Database\Factories;

use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InventoryLog>
 */
class InventoryLogFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'quantity_change' => fake()->numberBetween(-50, 50),
            'transaction_type' => fake()->randomElement(['purchase', 'sale', 'adjustment', 'return', 'waste']),
            'date' => fake()->dateTimeBetween('-6 months')->format('Y-m-d'),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
