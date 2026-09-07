<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Seed the application's roles.
     */
    public function run(): void
    {
        $descriptions = [
            RoleName::Admin->value => 'Full access to all data and settings.',
            RoleName::Manager->value => 'Can view and edit sales, expenses, and non-manager user accounts.',
            RoleName::Staff->value => 'Can create sales and expenses, and view their own records.',
            RoleName::Viewer->value => 'Read-only access to dashboards and reports.',
        ];

        foreach (RoleName::cases() as $role) {
            Role::query()->updateOrCreate(
                ['name' => $role->value],
                ['description' => $descriptions[$role->value]],
            );
        }
    }
}
