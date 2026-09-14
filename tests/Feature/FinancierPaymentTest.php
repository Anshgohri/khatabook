<?php

use App\Enums\RoleName;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\Role;
use App\Models\User;

test('financier role can be assigned to user and checked with isFinancier', function () {
    $financierRole = Role::firstOrCreate([
        'name' => RoleName::Financier->value,
    ], [
        'description' => 'Financier account',
    ]);

    $user = User::factory()->create([
        'role_id' => $financierRole->id,
    ]);

    expect($user->isFinancier())->toBeTrue();
    expect($user->role->name)->toBe('ROLE_FINANCIERS');
});

test('authenticated users can access the financiers page', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('financiers'));
    $response->assertOk();
});

test('interest only financier like Nitin Panipat keeps principal intact when interest EMI is paid', function () {
    $user = User::factory()->create();

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Nitin Panipat',
        'payout_type' => 'monthly',
        'interest_type' => 'interest_only',
        'default_payment_amount' => 3000.00,
    ]);

    // Record loan received of 60,000
    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->subMonths(2)->toDateString(),
        'type' => 'loan_received',
        'amount' => 60000.00,
        'payment_method' => 'bank_transfer',
        'notes' => 'Opening 60k loan taken',
    ]);

    // Record monthly payment (interest EMI) of 3,000
    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'monthly_payment',
        'amount' => 3000.00,
        'payment_method' => 'upi',
    ]);

    $financier->refresh();

    // Principal loan balance remains 60,000 (no deductions placed from interest payment)
    expect((float) $financier->outstanding_balance)->toEqual(60000.00);
    // Total paid should reflect 3,000
    expect((float) $financier->total_paid)->toEqual(3000.00);
});

test('principal reducing financier reduces balance on installment payment', function () {
    $user = User::factory()->create();

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Weekly Micro Finance',
        'payout_type' => 'weekly',
        'interest_type' => 'principal_reducing',
        'default_payment_amount' => 2000.00,
    ]);

    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->subWeeks(2)->toDateString(),
        'type' => 'loan_received',
        'amount' => 100000.00,
        'payment_method' => 'bank_transfer',
    ]);

    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'weekly_payment',
        'amount' => 2000.00,
        'payment_method' => 'upi',
    ]);

    $financier->refresh();

    expect($financier->payout_type)->toBe('weekly');
    expect((float) $financier->outstanding_balance)->toEqual(98000.00);
});

test('can create financier with initial loan amount opening balance', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Livewire::test('pages::khatabook.financiers')
        ->set('name', 'Rana Financier')
        ->set('phone', '9876543210')
        ->set('payout_type', 'daily')
        ->set('default_payment_amount', 2000.00)
        ->set('initial_loan_amount', 100000.00)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $financier = Financier::where('name', 'Rana Financier')->first();
    expect($financier)->not->toBeNull();
    expect((float) $financier->outstanding_balance)->toEqual(100000.00);
    expect($financier->payments)->toHaveCount(1);
    expect($financier->payments->first()->type)->toBe('loan_received');
});

test('only admin can delete financier', function () {
    $adminRole = Role::firstOrCreate(['name' => RoleName::Admin->value]);
    $staffRole = Role::firstOrCreate(['name' => RoleName::Staff->value]);

    $admin = User::factory()->create(['role_id' => $adminRole->id]);
    $staff = User::factory()->create(['role_id' => $staffRole->id]);

    $financier = Financier::factory()->create(['user_id' => $admin->id]);

    expect($staff->can('delete', $financier))->toBeFalse();
    expect($admin->can('delete', $financier))->toBeTrue();
});

test('can log daily payment via livewire form without error', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'payout_type' => 'daily',
        'default_payment_amount' => 500.00,
    ]);

    Livewire::test('pages::khatabook.financiers')
        ->call('openPaymentModal', $financier->id, 'daily_payment')
        ->set('amount', 500.00)
        ->set('payment_method', 'cash')
        ->set('payment_date', now()->toDateString())
        ->call('savePayment')
        ->assertHasNoErrors();

    expect($financier->fresh()->payments)->toHaveCount(1);
});

test('can update initial loan amount when editing financier', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Initial Test Financier',
        'payout_type' => 'daily',
        'default_payment_amount' => 500.00,
    ]);

    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'loan_received',
        'amount' => 50000.00,
        'payment_method' => 'cash',
        'notes' => 'Opening loan balance',
    ]);

    Livewire::test('pages::khatabook.financiers')
        ->call('editFinancier', $financier->id)
        ->assertSet('initial_loan_amount', 50000.00)
        ->set('initial_loan_amount', 75000.00)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $financier->refresh();
    expect((float) $financier->outstanding_balance)->toEqual(75000.00);
});

test('creates dashboard user in users table with first name based password when email provided', function () {
    $admin = User::factory()->create();
    $this->actingAs($admin);

    Livewire::test('pages::khatabook.financiers')
        ->set('name', 'Mahindra Finance')
        ->set('email', 'mahindra@example.com')
        ->set('phone', '9876543210')
        ->set('payout_type', 'monthly')
        ->set('default_payment_amount', 3000.00)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $userInDb = User::where('email', 'mahindra@example.com')->first();
    expect($userInDb)->not->toBeNull();
    expect($userInDb->isFinancier())->toBeTrue();
    expect(Auth::attempt(['email' => 'mahindra@example.com', 'password' => 'mahindra@123']))->toBeTrue();

    $financier = Financier::where('name', 'Mahindra Finance')->first();
    expect($financier)->not->toBeNull();
    expect($financier->financier_user_id)->toBe($userInDb->id);
});

test('financier role can only view products and their own loans', function () {
    $financierRole = Role::firstOrCreate(['name' => RoleName::Financier->value]);
    $financierUser = User::factory()->create(['role_id' => $financierRole->id]);

    $financier = Financier::factory()->create([
        'user_id' => User::factory()->create()->id,
        'financier_user_id' => $financierUser->id,
        'name' => 'Loan Partner',
    ]);

    $this->actingAs($financierUser);

    // Products page accessible
    $this->get(route('products'))->assertOk();

    // Financier ledger page accessible
    $this->get(route('financiers.show', $financier->id))->assertOk();

    // Sales page forbidden for Financier
    $this->get(route('sales'))->assertForbidden();

    // Expenses page forbidden for Financier
    $this->get(route('expenses'))->assertForbidden();

    // Employees page forbidden for Financier
    $this->get(route('employees'))->assertForbidden();
});
