<?php

namespace Database\Factories;

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductionLog>
 */
class ProductionLogFactory extends Factory
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
            'employee_id' => Employee::factory(),
            'finished_product_id' => Product::factory(),
            'quantity_produced' => fake()->numberBetween(1, 50),
            'raw_material_id' => Product::factory(),
            'raw_material_consumed_qty' => fake()->numberBetween(1, 100),
            'worker_wage' => fake()->randomElement([500.00, 800.00]),
            'date' => fake()->date(),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
