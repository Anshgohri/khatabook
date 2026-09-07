<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_category_id' => ProductCategory::factory(),
            'name' => fake()->unique()->words(3, true),
            'unit_price' => fake()->randomFloat(2, 10, 5000),
            'description' => fake()->sentence(),
            'stock_level' => fake()->numberBetween(0, 500),
        ];
    }
}
