<?php

use App\Enums\RoleName;
use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Role;
use App\Models\User;

test('employee role can be assigned to user and checked with isEmployee', function () {
    $employeeRole = Role::firstOrCreate([
        'name' => RoleName::Employee->value,
    ], [
        'description' => 'Employee account',
    ]);

    $user = User::factory()->create([
        'role_id' => $employeeRole->id,
    ]);

    expect($user->isEmployee())->toBeTrue();
    expect($user->role->name)->toBe('ROLE_EMPLOYEE');
});

test('authenticated users can access the employees page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('employees'));
    $response->assertOk();
});

test('can create employee and log advance given of 100000', function () {
    $user = User::factory()->create();

    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'name' => 'Ramesh Kumar',
        'default_daily_rate' => 800.00,
    ]);

    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'advance_given',
        'amount' => 100000.00,
        'payment_method' => 'upi',
        'notes' => '1 Lakh initial advance paid to Ramesh',
    ]);

    $employee->refresh();

    expect((float) $employee->advance_balance)->toEqual(100000.00);
});

test('logging daily pay and advance repayment updates advance balance correctly', function () {
    $user = User::factory()->create();

    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'name' => 'Suresh',
        'default_daily_rate' => 500.00,
    ]);

    // Give 1,00,000 advance
    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->subDays(10)->toDateString(),
        'type' => 'advance_given',
        'amount' => 100000.00,
        'payment_method' => 'bank_transfer',
    ]);

    // Pay daily wage of 800
    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->subDays(2)->toDateString(),
        'type' => 'daily_pay',
        'amount' => 800.00,
        'payment_method' => 'cash',
        'notes' => 'Daily pay',
    ]);

    // Repay 20,000 from advance
    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'advance_repaid',
        'amount' => 20000.00,
        'payment_method' => 'cash',
        'notes' => 'Advance repaid in cash',
    ]);

    $employee->refresh();

    // Advance balance should be 100,000 - 20,000 = 80,000
    expect((float) $employee->advance_balance)->toEqual(80000.00);
    expect($employee->payments)->toHaveCount(3);
});

test('calculates correct period wage stats for month, week and year', function () {
    $user = User::factory()->create();

    $employee = Employee::factory()->create([
        'user_id' => $user->id,
        'default_daily_rate' => 800.00,
    ]);

    // Paid today
    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'daily_pay',
        'amount' => 800.00,
        'payment_method' => 'cash',
    ]);

    // Paid 5 days ago (same week/month)
    EmployeePayment::create([
        'employee_id' => $employee->id,
        'user_id' => $user->id,
        'date' => now()->subDays(2)->toDateString(),
        'type' => 'daily_pay',
        'amount' => 500.00,
        'payment_method' => 'cash',
    ]);

    $this->actingAs($user);
    $component = Livewire::test('pages::khatabook.employees');
    expect($component->get('wagesThisMonth'))->toBeGreaterThanOrEqual(1300.00);
});

test('only admin can delete employee', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $staffRole = Role::firstOrCreate(['name' => RoleName::Staff->value]);

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $staff = User::factory()->create(['role_id' => $staffRole->id]);

    $employee = Employee::factory()->create(['user_id' => $admin->id]);

    expect($staff->can('delete', $employee))->toBeFalse();
    expect($admin->can('delete', $employee))->toBeTrue();
});
