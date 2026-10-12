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

    Livewire::test('pages::financiers')
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

    Livewire::test('pages::financiers')
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

    Livewire::test('pages::financiers')
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

    Livewire::test('pages::financiers')
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

test('registered users like admins and managers appear in the dropdown list when adding a financier', function () {
    $admin = User::factory()->create(['name' => 'Admin Boss', 'role_id' => Role::firstOrCreate(['name' => RoleName::Admin->value])->id]);
    $manager = User::factory()->create(['name' => 'Manager Alex', 'role_id' => Role::firstOrCreate(['name' => RoleName::Manager->value])->id]);
    $dummyCustomer = User::factory()->create(['name' => 'Dummy Customer', 'email' => 'cust_12345@khatabook.customer']);

    $this->actingAs($admin);

    $component = Livewire::test('pages::financiers');
    $registeredUsers = $component->get('registeredUsers');

    expect($registeredUsers->pluck('id'))->toContain($admin->id);
    expect($registeredUsers->pluck('id'))->toContain($manager->id);
    expect($registeredUsers->pluck('id'))->not->toContain($dummyCustomer->id);
});

test('selecting an already registered manager auto-fills form and links them as financier without error', function () {
    $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => RoleName::Admin->value])->id]);
    $managerRole = Role::firstOrCreate(['name' => RoleName::Manager->value]);
    $manager = User::factory()->create([
        'name' => 'Sanjay Manager',
        'email' => 'sanjay.manager@example.com',
        'phone' => '9876543210',
        'role_id' => $managerRole->id,
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::financiers')
        ->call('createFinancier')
        ->set('financier_user_id', (string) $manager->id)
        ->assertSet('name', 'Sanjay Manager')
        ->assertSet('email', 'sanjay.manager@example.com')
        ->assertSet('phone', '9876543210')
        ->set('payout_type', 'daily')
        ->set('default_payment_amount', 1500.00)
        ->set('initial_loan_amount', 50000.00)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $financier = Financier::where('financier_user_id', $manager->id)->first();
    expect($financier)->not->toBeNull();
    expect($financier->name)->toBe('Sanjay Manager');
    expect((float) $financier->outstanding_balance)->toEqual(50000.00);

    // Verify manager account preserved its role and didn't get overwritten
    $manager->refresh();
    expect($manager->role->name)->toBe(RoleName::Manager->value);
    expect($manager->isManager())->toBeTrue();
});

test('typing an existing registered user email auto-matches and links user without unique email error', function () {
    $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => RoleName::Admin->value])->id]);
    $existingStaff = User::factory()->create([
        'name' => 'Vikram Staff',
        'email' => 'vikram@company.com',
        'phone' => '9988776655',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::financiers')
        ->set('name', 'Vikram Financier Services')
        ->set('email', 'vikram@company.com')
        ->set('phone', '9988776655')
        ->set('payout_type', 'monthly')
        ->set('default_payment_amount', 2500.00)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $financier = Financier::where('name', 'Vikram Financier Services')->first();
    expect($financier)->not->toBeNull();
    expect($financier->financier_user_id)->toBe($existingStaff->id);
});

test('user can update financier payout history from financiers page', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $financier = Financier::factory()->create([
        'user_id' => $admin->id,
        'payout_type' => 'daily',
        'interest_type' => 'principal_reducing',
        'default_payment_amount' => 500,
    ]);

    $payment = FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'type' => 'daily_payment',
        'amount' => 500.00,
        'payment_method' => 'cash',
        'notes' => 'Original payment note',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::financiers')
        ->call('editPayment', $payment->id)
        ->assertSet('editingPaymentId', $payment->id)
        ->assertSet('amount', 500.00)
        ->set('amount', 750.00)
        ->set('payment_notes', 'Updated payment note')
        ->call('savePayment')
        ->assertHasNoErrors()
        ->assertSet('editingPaymentId', null)
        ->assertSet('showPaymentModal', false);

    $payment->refresh();
    expect((float) $payment->amount)->toBe(750.00);
    expect($payment->notes)->toBe('Updated payment note');
});

test('user can update financier payout history from financier ledger page', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $financier = Financier::factory()->create([
        'user_id' => $admin->id,
        'payout_type' => 'monthly',
        'interest_type' => 'principal_reducing',
        'default_payment_amount' => 2000,
    ]);

    $payment = FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'type' => 'monthly_payment',
        'amount' => 2000.00,
        'payment_method' => 'bank_transfer',
        'notes' => 'Old ledger note',
    ]);

    $this->actingAs($admin);

    Livewire::test('pages::financier-ledger', ['financier' => $financier])
        ->call('editPayment', $payment->id)
        ->assertSet('editingPaymentId', $payment->id)
        ->assertSet('amount', 2000.00)
        ->set('amount', 2500.00)
        ->set('payment_notes', 'New ledger note')
        ->call('savePayment')
        ->assertHasNoErrors()
        ->assertSet('editingPaymentId', null)
        ->assertSet('showPaymentModal', false);

    $payment->refresh();
    expect((float) $payment->amount)->toBe(2500.00);
    expect($payment->notes)->toBe('New ledger note');
});

test('financier cannot add finance log or edit payments and cannot see action buttons on ledger', function () {
    $financierRole = Role::firstOrCreate(['name' => RoleName::Financier->value]);
    $financierUser = User::factory()->create(['role_id' => $financierRole->id]);

    $admin = User::factory()->create(['role_id' => Role::firstOrCreate(['name' => RoleName::Admin->value])->id]);
    $financier = Financier::factory()->create([
        'user_id' => $admin->id,
        'financier_user_id' => $financierUser->id,
        'name' => 'Self Finance Co',
        'payout_type' => 'daily',
        'default_payment_amount' => 500.00,
    ]);

    $payment = FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'type' => 'daily_payment',
        'amount' => 500.00,
        'payment_method' => 'cash',
        'notes' => 'Existing admin log',
    ]);

    // Financier viewing ledger page:
    // 1. Can view ledger
    // 2. Does NOT see "Log Payment / Loan" or "Pay Installment" buttons
    Livewire::actingAs($financierUser)
        ->test('pages::financier-ledger', ['financier' => $financier])
        ->assertOk()
        ->assertDontSee('Log Payment / Loan')
        ->assertDontSee('Pay Installment');

    // Financier trying to call openPaymentModal must be forbidden
    Livewire::actingAs($financierUser)
        ->test('pages::financier-ledger', ['financier' => $financier])
        ->call('openPaymentModal')
        ->assertForbidden();

    // Financier trying to call savePayment must be forbidden
    Livewire::actingAs($financierUser)
        ->test('pages::financier-ledger', ['financier' => $financier])
        ->set('amount', 500.00)
        ->set('payment_date', now()->toDateString())
        ->set('type', 'daily_payment')
        ->set('payment_method', 'cash')
        ->call('savePayment')
        ->assertForbidden();

    // Financier trying to edit or delete payment must be forbidden
    Livewire::actingAs($financierUser)
        ->test('pages::financier-ledger', ['financier' => $financier])
        ->call('editPayment', $payment->id)
        ->assertForbidden();

    Livewire::actingAs($financierUser)
        ->test('pages::financier-ledger', ['financier' => $financier])
        ->call('deletePayment', $payment->id)
        ->assertForbidden();

    // Financier accessing pages::financiers is redirected to their own ledger
    Livewire::actingAs($financierUser)
        ->test('pages::financiers')
        ->assertRedirect(route('financiers.show', $financier->id));

    // Financier cannot create a financier
    expect($financierUser->can('create', Financier::class))->toBeFalse();
    expect($financierUser->can('update', $financier))->toBeFalse();
    expect($financierUser->can('delete', $financier))->toBeFalse();
});


