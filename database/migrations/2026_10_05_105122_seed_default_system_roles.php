<?php

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $descriptions = [
            RoleName::SystemAdmin->value => 'System Admin with full unrestricted access to all expenses, users, categories, and settings.',
            RoleName::Admin->value => 'Full access to all data and settings.',
            RoleName::Manager->value => 'Can view and edit sales, expenses, and non-manager user accounts.',
            RoleName::Staff->value => 'Can create sales and expenses, and view their own records.',
            RoleName::Viewer->value => 'Read-only access to dashboards and reports.',
            RoleName::Employee->value => 'Employee account for viewing payments and advance details.',
            RoleName::Financier->value => 'Financier account for tracking daily/monthly payout details.',
            RoleName::Customer->value => 'Customer account for tracking customer sales and details.',
        ];

        foreach (RoleName::cases() as $role) {
            Role::firstOrCreate(
                ['name' => $role->value],
                ['description' => $descriptions[$role->value] ?? null]
            );
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
