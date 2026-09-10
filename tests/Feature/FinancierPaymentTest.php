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

test('can create financier and log daily and monthly payments correctly', function () {
    $user = User::factory()->create();

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Mahavir Finance',
        'payout_type' => 'daily',
        'default_payment_amount' => 500.00,
    ]);

    // Record loan received of 50000
    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->subDays(5)->toDateString(),
        'type' => 'loan_received',
        'amount' => 50000.00,
        'payment_method' => 'bank_transfer',
        'notes' => '50k loan taken from Mahavir Finance',
    ]);

    // Record daily payment of 500
    FinancierPayment::create([
        'financier_id' => $financier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'daily_payment',
        'amount' => 500.00,
        'payment_method' => 'upi',
    ]);

    $financier->refresh();

    // Balance should be 50,000 - 500 = 49,500
    expect((float) $financier->outstanding_balance)->toEqual(49500.00);
});

test('can create weekly paid financier and log weekly payment', function () {
    $user = User::factory()->create();

    $financier = Financier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Weekly Micro Finance',
        'payout_type' => 'weekly',
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
