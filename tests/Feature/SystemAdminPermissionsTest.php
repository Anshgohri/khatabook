<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('only system admin can update a user role', function () {
    $sysRole = Role::firstOrCreate(['name' => RoleName::SystemAdmin->value]);
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $staffRole = Role::firstOrCreate(['name' => RoleName::Staff->value]);
    $managerRole = Role::firstOrCreate(['name' => RoleName::Manager->value]);

    $systemAdmin = User::factory()->create(['role_id' => $sysRole->id]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $targetUser = User::factory()->create(['role_id' => $staffRole->id]);

    // Regular admin trying to change targetUser's role should fail
    Livewire::actingAs($admin)
        ->test('pages::users')
        ->set('editingId', $targetUser->id)
        ->set('edit_name', $targetUser->name)
        ->set('edit_role_id', (string) $managerRole->id)
        ->set('edit_status', 'active')
        ->call('saveUser')
        ->assertHasErrors(['edit_role_id']);

    // System Admin changing targetUser's role should succeed
    Livewire::actingAs($systemAdmin)
        ->test('pages::users')
        ->set('editingId', $targetUser->id)
        ->set('edit_name', $targetUser->name)
        ->set('edit_role_id', (string) $managerRole->id)
        ->set('edit_status', 'active')
        ->call('saveUser')
        ->assertHasNoErrors();

    expect($targetUser->fresh()->role_id)->toBe($managerRole->id);
});

test('profit figures are only visible to system admin', function () {
    $sysRole = Role::firstOrCreate(['name' => RoleName::SystemAdmin->value]);
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);

    $systemAdmin = User::factory()->create(['role_id' => $sysRole->id]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    // System admin sees profit
    Livewire::actingAs($systemAdmin)
        ->test('pages::product-sales-report')
        ->assertSee('Total Sales Profit')
        ->assertSee('Gross Profit');

    // Regular admin does NOT see profit
    Livewire::actingAs($admin)
        ->test('pages::product-sales-report')
        ->assertDontSee('Total Sales Profit')
        ->assertDontSee('Gross Profit');
});
