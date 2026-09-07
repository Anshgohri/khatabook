<?php

namespace Database\Factories;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Permission>
 */
class PermissionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'role_id' => Role::factory(),
            'action' => fake()->randomElement(['view', 'create', 'update', 'delete']),
            'resource' => fake()->randomElement(['sales', 'expenses', 'products', 'users']),
        ];
    }
}
