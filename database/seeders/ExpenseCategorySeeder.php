<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Seed the application's expense categories.
     */
    public function run(): void
    {
        collect(['Raw Materials', 'Labor', 'Transport', 'Utilities', 'Other'])
            ->each(fn (string $name) => ExpenseCategory::query()->firstOrCreate(['name' => $name]));
    }
}
