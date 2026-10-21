<?php

use App\Enums\RoleName;
use App\Models\Employee;
use App\Models\Role;
use App\Models\User;
use Livewire\Livewire;

test('authenticated admin or manager can access employee rates page', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $employee = Employee::factory()->create([
        'user_id' => $admin->id,
        'name' => 'Babu Lal Mistri',
        'ghodi_medium_rate' => 11.00,
        'ghodi_heavy_rate' => 12.00,
        'siddi_danda_rate' => 4.00,
        'challi_rate' => 20.00,
    ]);

    Livewire::actingAs($admin)
        ->test('pages::employee-rates')
        ->assertOk()
        ->assertSee('Employee Piece Rates')
        ->assertSee('Babu Lal Mistri')
        ->assertSee('₹11.00')
        ->assertSee('₹12.00')
        ->assertSee('₹4.00')
        ->assertSee('₹20.00');
});

test('can update piece rates for an employee via livewire modal', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $employee = Employee::factory()->create([
        'user_id' => $admin->id,
        'name' => 'Kishore Kumar',
        'ghodi_medium_rate' => 11.00,
        'ghodi_heavy_rate' => 12.00,
        'siddi_danda_rate' => 4.00,
        'challi_rate' => 20.00,
    ]);

    Livewire::actingAs($admin)
        ->test('pages::employee-rates')
        ->call('editRates', $employee->id)
        ->assertSet('editingEmployeeId', $employee->id)
        ->assertSet('ghodi_medium_rate', 11.00)
        ->set('ghodi_medium_rate', 13.50)
        ->set('ghodi_heavy_rate', 14.50)
        ->set('siddi_danda_rate', 5.00)
        ->set('challi_rate', 25.00)
        ->call('saveRates')
        ->assertHasNoErrors();

    $employee->refresh();
    expect((float) $employee->ghodi_medium_rate)->toBe(13.50);
    expect((float) $employee->ghodi_heavy_rate)->toBe(14.50);
    expect((float) $employee->siddi_danda_rate)->toBe(5.00);
    expect((float) $employee->challi_rate)->toBe(25.00);
});

test('interactive calculator simulates accurate giving cost for ghodi, siddi, and challi', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $admin = User::factory()->create(['role_id' => $adminRole->id]);

    $component = Livewire::actingAs($admin)
        ->test('pages::employee-rates');

    // 1. Medium Ghodi (5 feet, 1 piece): 5 * 11 = 55
    $component->set('calcItemType', 'ghodi_medium')
        ->set('calcGhodiFeet', 5)
        ->set('calcQuantity', 1);
    expect($component->get('simulatedCost')['total_cost'])->toBe(55.0);

    // 2. Heavy Ghodi (6 feet, 2 pieces): 6 * 12 * 2 = 144
    $component->set('calcItemType', 'ghodi_heavy')
        ->set('calcGhodiFeet', 6)
        ->set('calcQuantity', 2);
    expect($component->get('simulatedCost')['total_cost'])->toBe(144.0);

    // 3. Siddi 12 feet (10 dandas, 1 piece): 10 * 4 = 40
    $component->set('calcItemType', 'siddi')
        ->set('calcSiddiFeet', 12)
        ->set('calcQuantity', 1);
    expect($component->get('simulatedCost')['total_cost'])->toBe(40.0);

    // 4. Siddi 15 feet (13 dandas, 3 pieces): 13 * 4 * 3 = 156
    $component->set('calcItemType', 'siddi')
        ->set('calcSiddiFeet', 15)
        ->set('calcQuantity', 3);
    expect($component->get('simulatedCost')['total_cost'])->toBe(156.0);

    // 5. Challi (4 pieces): 4 * 20 = 80
    $component->set('calcItemType', 'challi')
        ->set('calcQuantity', 4);
    expect($component->get('simulatedCost')['total_cost'])->toBe(80.0);
});
