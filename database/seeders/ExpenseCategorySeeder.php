<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Seed the application's expense categories with rich defaults.
     */
    public function run(): void
    {
        $categories = [
            [
                'name'        => 'Raw Materials',
                'icon'        => '📦',
                'color'       => 'amber',
                'type'        => 'business',
                'description' => 'Purchase of raw materials like Baans, Fatta, bamboo, etc.',
            ],
            [
                'name'        => 'Labor / Wages',
                'icon'        => '👷',
                'color'       => 'indigo',
                'type'        => 'business',
                'description' => 'Daily wages, piece-rate payments and labour charges.',
            ],
            [
                'name'        => 'Transport / Freight',
                'icon'        => '🚚',
                'color'       => 'sky',
                'type'        => 'business',
                'description' => 'Freight, loading/unloading and vehicle transport costs.',
            ],
            [
                'name'        => 'Utilities / Power',
                'icon'        => '⚡',
                'color'       => 'rose',
                'type'        => 'business',
                'description' => 'Electricity, water, fuel and utility bills.',
            ],
            [
                'name'        => 'Daily Consumables',
                'icon'        => '🛒',
                'color'       => 'emerald',
                'type'        => 'consumable',
                'description' => 'Tea, snacks, daily groceries and consumable supplies.',
            ],
            [
                'name'        => 'Household Expenses',
                'icon'        => '🏠',
                'color'       => 'purple',
                'type'        => 'household',
                'description' => 'Household grocery and maintenance expenses.',
            ],
            [
                'name'        => 'Tools / Maintenance',
                'icon'        => '🛠️',
                'color'       => 'amber',
                'type'        => 'business',
                'description' => 'Repair, maintenance and tool purchases for the workshop.',
            ],
            [
                'name'        => 'Financial / Loan',
                'icon'        => '💰',
                'color'       => 'rose',
                'type'        => 'personal',
                'description' => 'Loan repayments, interest payments and financial charges.',
            ],
            [
                'name'        => 'Other',
                'icon'        => '📁',
                'color'       => 'emerald',
                'type'        => 'business',
                'description' => 'Miscellaneous expenses not covered by other categories.',
            ],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::query()->firstOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
