<?php

use App\Models\ExpenseCategory;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Seed default expense categories and update any old bare-named ones.
     */
    public function up(): void
    {
        // Remove old bare-seeded names that were created without icon/color/type
        // and replace them with the richer versions below
        $oldNames = ['Raw Materials', 'Labor', 'Transport', 'Utilities', 'Other'];
        foreach ($oldNames as $old) {
            $existing = ExpenseCategory::where('name', $old)->first();
            if ($existing && ! $existing->icon) {
                $existing->delete();
            }
        }

        $defaults = [
            ['name' => 'Raw Materials',       'icon' => '📦', 'color' => 'amber',  'type' => 'business',   'description' => 'Purchase of raw materials like Baans, Fatta, bamboo, etc.'],
            ['name' => 'Labor / Wages',        'icon' => '👷', 'color' => 'indigo', 'type' => 'business',   'description' => 'Daily wages, piece-rate payments and labour charges.'],
            ['name' => 'Transport / Freight',  'icon' => '🚚', 'color' => 'sky',    'type' => 'business',   'description' => 'Freight, loading/unloading and vehicle transport costs.'],
            ['name' => 'Utilities / Power',    'icon' => '⚡', 'color' => 'rose',   'type' => 'business',   'description' => 'Electricity, water, fuel and utility bills.'],
            ['name' => 'Daily Consumables',    'icon' => '🛒', 'color' => 'emerald','type' => 'consumable', 'description' => 'Tea, snacks, daily groceries and consumable supplies.'],
            ['name' => 'Household Expenses',   'icon' => '🏠', 'color' => 'purple', 'type' => 'household',  'description' => 'Household grocery and maintenance expenses.'],
            ['name' => 'Tools / Maintenance',  'icon' => '🛠️','color' => 'amber',  'type' => 'business',   'description' => 'Repair, maintenance and tool purchases for the workshop.'],
            ['name' => 'Financial / Loan',     'icon' => '💰', 'color' => 'rose',   'type' => 'personal',   'description' => 'Loan repayments, interest payments and financial charges.'],
            ['name' => 'Other',                'icon' => '📁', 'color' => 'emerald','type' => 'business',   'description' => 'Miscellaneous expenses not covered by other categories.'],
        ];

        foreach ($defaults as $cat) {
            ExpenseCategory::firstOrCreate(['name' => $cat['name']], $cat);
        }
    }

    /**
     * Remove default seeded categories (only if they have no expenses).
     */
    public function down(): void
    {
        $names = [
            'Raw Materials', 'Labor / Wages', 'Transport / Freight',
            'Utilities / Power', 'Daily Consumables', 'Household Expenses',
            'Tools / Maintenance', 'Financial / Loan', 'Other',
        ];

        ExpenseCategory::whereIn('name', $names)
            ->whereDoesntHave('expenses')
            ->delete();
    }
};
