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

test('system admin can update user email and contact number', function () {
    $sysRole = Role::firstOrCreate(['name' => RoleName::SystemAdmin->value]);
    $staffRole = Role::firstOrCreate(['name' => RoleName::Staff->value]);

    $systemAdmin = User::factory()->create(['role_id' => $sysRole->id]);
    $targetUser = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'phone' => '9876543210',
        'role_id' => $staffRole->id,
    ]);

    Livewire::actingAs($systemAdmin)
        ->test('pages::users')
        ->call('editUser', $targetUser->id)
        ->set('edit_email', 'updated.email@example.com')
        ->set('edit_phone', '9123456789')
        ->call('saveUser')
        ->assertHasNoErrors();

    $targetUser->refresh();
    expect($targetUser->email)->toBe('updated.email@example.com')
        ->and($targetUser->phone)->toBe('9123456789');
});

test('non system admin cannot update user email or contact number', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $staffRole = Role::firstOrCreate(['name' => RoleName::Staff->value]);

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $targetUser = User::factory()->create([
        'name' => 'Original Name',
        'email' => 'original@example.com',
        'phone' => '9876543210',
        'role_id' => $staffRole->id,
    ]);

    // Regular admin trying to change email should fail
    Livewire::actingAs($admin)
        ->test('pages::users')
        ->call('editUser', $targetUser->id)
        ->set('edit_email', 'hacked@example.com')
        ->call('saveUser')
        ->assertHasErrors(['edit_email']);

    expect($targetUser->fresh()->email)->toBe('original@example.com');

    // Regular admin trying to change contact number should fail
    Livewire::actingAs($admin)
        ->test('pages::users')
        ->call('editUser', $targetUser->id)
        ->set('edit_phone', '9111222333')
        ->call('saveUser')
        ->assertHasErrors(['edit_phone']);

    expect($targetUser->fresh()->phone)->toBe('9876543210');
});

test('system admin updating user email and contact number syncs linked financier and supplier records', function () {
    $sysRole = Role::firstOrCreate(['name' => RoleName::SystemAdmin->value]);
    $financierRole = Role::firstOrCreate(['name' => RoleName::Financier->value]);

    $systemAdmin = User::factory()->create(['role_id' => $sysRole->id]);
    $targetUser = User::factory()->create([
        'name' => 'Partner User',
        'email' => 'partner@example.com',
        'phone' => '9888877777',
        'role_id' => $financierRole->id,
    ]);

    $financier = \App\Models\Financier::factory()->create([
        'user_id' => $systemAdmin->id,
        'financier_user_id' => $targetUser->id,
        'name' => 'Partner User',
        'phone' => '9888877777',
    ]);

    $supplier = \App\Models\Supplier::factory()->create([
        'user_id' => $systemAdmin->id,
        'supplier_user_id' => $targetUser->id,
        'name' => 'Partner User',
        'email' => 'partner@example.com',
        'phone' => '9888877777',
    ]);

    Livewire::actingAs($systemAdmin)
        ->test('pages::users')
        ->call('editUser', $targetUser->id)
        ->set('edit_name', 'Renamed Partner')
        ->set('edit_email', 'newpartner@example.com')
        ->set('edit_phone', '9999900000')
        ->call('saveUser')
        ->assertHasNoErrors();

    $financier->refresh();
    $supplier->refresh();

    expect($financier->name)->toBe('Renamed Partner')
        ->and($financier->phone)->toBe('9999900000')
        ->and($supplier->name)->toBe('Renamed Partner')
        ->and($supplier->email)->toBe('newpartner@example.com')
        ->and($supplier->phone)->toBe('9999900000');
});

