<?php

use App\Models\Employee;
use App\Models\EmployeePayment;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Livewire\Livewire;

test('guests are redirected to login when visiting ledger pages', function () {
    $employee = Employee::factory()->create();
    $financier = Financier::factory()->create();
    $supplier = Supplier::factory()->create();

    $this->get(route('employees.show', $employee->id))->assertRedirect(route('login'));
    $this->get(route('financiers.show', $financier->id))->assertRedirect(route('login'));
    $this->get(route('suppliers.show', $supplier->id))->assertRedirect(route('login'));
});

test('authenticated users can access employee ledger page and log payments', function () {
    $user = User::factory()->create();
    $employee = Employee::factory()->create(['user_id' => $user->id, 'default_daily_rate' => 800]);

    $this->actingAs($user)
        ->get(route('employees.show', $employee->id))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::khatabook.employee-ledger', ['employee' => $employee])
        ->set('type', 'daily_pay')
        ->set('amount', 800)
        ->set('payment_date', now()->toDateString())
        ->set('payment_method', 'cash')
        ->call('savePayment')
        ->assertHasNoErrors();

    expect(EmployeePayment::where('employee_id', $employee->id)->count())->toBe(1);
});

test('authenticated users can access financier ledger page and log payments', function () {
    $user = User::factory()->create();
    $financier = Financier::factory()->create(['user_id' => $user->id, 'default_payment_amount' => 1000]);

    $this->actingAs($user)
        ->get(route('financiers.show', $financier->id))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::khatabook.financier-ledger', ['financier' => $financier])
        ->set('type', 'daily_payment')
        ->set('amount', 1000)
        ->set('payment_date', now()->toDateString())
        ->set('payment_method', 'cash')
        ->call('savePayment')
        ->assertHasNoErrors();

    expect(FinancierPayment::where('financier_id', $financier->id)->count())->toBe(1);
});

test('authenticated users can access supplier ledger page and record raw material purchases', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['user_id' => $user->id]);

    $this->actingAs($user)
        ->get(route('suppliers.show', $supplier->id))
        ->assertOk();

    Livewire::actingAs($user)
        ->test('pages::khatabook.supplier-ledger', ['supplier' => $supplier])
        ->set('type', 'raw_material_purchase')
        ->set('amount', 15000)
        ->set('payment_date', now()->toDateString())
        ->set('payment_method', 'bank_transfer')
        ->set('payment_notes', '200 Bans purchased')
        ->call('savePayment')
        ->assertHasNoErrors();

    expect(SupplierPayment::where('supplier_id', $supplier->id)->count())->toBe(1);
    expect((float) $supplier->fresh()->outstanding_balance)->toBe(15000.0);
});
