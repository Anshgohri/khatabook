<?php

use App\Models\Expense;
use App\Models\ExpenseCategory;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
});

test('user can upload a bill image when saving a supplier payment entry', function () {
    $user = User::factory()->create();
    $supplier = Supplier::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('supplier_bill.jpg');

    Livewire::actingAs($user)
        ->test('pages::khatabook.suppliers')
        ->set('paymentSupplierId', $supplier->id)
        ->set('type', 'raw_material_purchase')
        ->set('amount', 25000)
        ->set('payment_method', 'bank_transfer')
        ->set('payment_date', now()->toDateString())
        ->set('payment_notes', '200 Bans bought from Raja Assam')
        ->set('bill_image', $file)
        ->call('savePayment');

    $payment = SupplierPayment::where('supplier_id', $supplier->id)->latest()->first();

    expect($payment)->not->toBeNull();
    expect($payment->bill_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->bill_path);
});

test('user can upload a bill image when recording a financier payment', function () {
    $user = User::factory()->create();
    $financier = Financier::factory()->create(['user_id' => $user->id]);

    $file = UploadedFile::fake()->image('financier_receipt.png');

    Livewire::actingAs($user)
        ->test('pages::khatabook.financiers')
        ->set('paymentFinancierId', $financier->id)
        ->set('type', 'daily_payment')
        ->set('amount', 1000)
        ->set('payment_method', 'upi')
        ->set('payment_date', now()->toDateString())
        ->set('bill_image', $file)
        ->call('savePayment');

    $payment = FinancierPayment::where('financier_id', $financier->id)->latest()->first();

    expect($payment)->not->toBeNull();
    expect($payment->bill_path)->not->toBeNull();
    Storage::disk('public')->assertExists($payment->bill_path);
});

test('user can upload a receipt photo when creating an expense entry', function () {
    $user = User::factory()->create();
    $category = ExpenseCategory::factory()->create();

    $file = UploadedFile::fake()->create('receipt.pdf', 100, 'application/pdf');

    Livewire::actingAs($user)
        ->test('pages::khatabook.expenses')
        ->set('date', now()->toDateString())
        ->set('expense_category_id', $category->id)
        ->set('description', 'Machine repair & consumables')
        ->set('amount', 4500)
        ->set('payment_method', 'cash')
        ->set('bill_image', $file)
        ->call('save');

    $expense = Expense::where('user_id', $user->id)->latest()->first();

    expect($expense)->not->toBeNull();
    expect($expense->bill_path)->not->toBeNull();
    Storage::disk('public')->assertExists($expense->bill_path);
});
