<?php

namespace Database\Seeders;

use App\Models\ProductCategory;
use Illuminate\Database\Seeder;

class ProductCategorySeeder extends Seeder
{
    /**
     * Seed the application's product categories.
     */
    public function run(): void
    {
        collect(['Bamboo Poles', 'Matting', 'Baskets', 'Furniture', 'Other'])
            ->each(fn (string $name) => ProductCategory::query()->firstOrCreate(['name' => $name]));
    }
}
