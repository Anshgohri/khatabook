<?php

use App\Enums\RoleName;
use App\Models\Financier;
use App\Models\Product;
use App\Models\Role;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Livewire\Livewire;

test('adding a sale automatically creates a user with ROLE_CUSTOMER and links it', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $prod = Product::factory()->create(['unit_price' => 250]);

    Livewire::actingAs($admin)
        ->test('pages::sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Rahul Sharma')
        ->set('customer_phone', '9876543210')
        ->set('customer_city', 'Mohali')
        ->set('saleItems', [
            ['product_id' => $prod->id, 'quantity' => 2, 'unit_price' => 250, 'total_price' => 500],
        ])
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $customerUser = User::where('phone', '9876543210')->first();
    expect($customerUser)->not->toBeNull();
    expect($customerUser->name)->toBe('Rahul Sharma');
    expect($customerUser->role?->name)->toBe(RoleName::Customer->value);

    $sale = Sale::latest()->first();
    expect($sale->customer_id)->toBe($customerUser->id);
});

test('adding a financier creates a user with ROLE_FINANCIERS and links it', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    Livewire::actingAs($admin)
        ->test('pages::financiers')
        ->set('name', 'Pooja Finance')
        ->set('email', 'pooja@finance.com')
        ->set('phone', '9988776655')
        ->set('payout_type', 'daily')
        ->set('interest_type', 'interest_only')
        ->set('default_payment_amount', 500)
        ->set('initial_loan_amount', 50000)
        ->call('saveFinancier')
        ->assertHasNoErrors();

    $financierUser = User::where('email', 'pooja@finance.com')->first();
    expect($financierUser)->not->toBeNull();
    expect($financierUser->role?->name)->toBe(RoleName::Financier->value);

    $financier = Financier::where('name', 'Pooja Finance')->first();
    expect($financier)->not->toBeNull();
    expect($financier->financier_user_id)->toBe($financierUser->id);
});

test('adding a supplier creates a user with ROLE_SUPPLIERS and links it', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    Livewire::actingAs($admin)
        ->test('pages::suppliers')
        ->set('name', 'Raja Assam Raw Woods')
        ->set('email', 'raja@bamboo.com')
        ->set('phone', '9811223344')
        ->set('location', 'Assam')
        ->set('material_supplied', 'Baans')
        ->set('initial_balance', 15000)
        ->call('saveSupplier')
        ->assertHasNoErrors();

    $supplierUser = User::where('email', 'raja@bamboo.com')->first();
    expect($supplierUser)->not->toBeNull();
    expect($supplierUser->role?->name)->toBe(RoleName::Supplier->value);

    $supplier = Supplier::where('name', 'Raja Assam Raw Woods')->first();
    expect($supplier)->not->toBeNull();
    expect($supplier->supplier_user_id)->toBe($supplierUser->id);
});

test('sync button on financiers component links unlinked financiers to new user entries with ROLE_FINANCIERS', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    // Create unlinked financier directly
    $financier = Financier::create([
        'user_id' => $admin->id,
        'name' => 'Kisan Mudra',
        'phone' => '9123456789',
        'payout_type' => 'monthly',
        'default_payment_amount' => 1000,
        'outstanding_balance' => 0,
        'status' => 'active',
        'financier_user_id' => null,
    ]);

    expect($financier->financier_user_id)->toBeNull();

    Livewire::actingAs($admin)
        ->test('pages::financiers')
        ->call('syncFinancierUser', $financier->id);

    $financier->refresh();
    expect($financier->financier_user_id)->not->toBeNull();

    $user = User::find($financier->financier_user_id);
    expect($user)->not->toBeNull();
    expect($user->role?->name)->toBe(RoleName::Financier->value);
});

test('sync button on suppliers component links unlinked suppliers to new user entries with ROLE_SUPPLIERS', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    // Create unlinked supplier directly
    $supplier = Supplier::create([
        'user_id' => $admin->id,
        'name' => 'Suraj Timber Depot',
        'phone' => '9871122334',
        'location' => 'Punjab',
        'material_supplied' => 'Fatta',
        'outstanding_balance' => 0,
        'status' => 'active',
        'supplier_user_id' => null,
    ]);

    expect($supplier->supplier_user_id)->toBeNull();

    Livewire::actingAs($admin)
        ->test('pages::suppliers')
        ->call('syncSupplierUser', $supplier->id);

    $supplier->refresh();
    expect($supplier->supplier_user_id)->not->toBeNull();

    $user = User::find($supplier->supplier_user_id);
    expect($user)->not->toBeNull();
    expect($user->role?->name)->toBe(RoleName::Supplier->value);
});

test('sync button on sales component links unlinked sales to customer user accounts with ROLE_CUSTOMER', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    // Create unlinked sale directly
    $sale = Sale::create([
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'customer_name' => 'Gurpreet Singh',
        'quantity' => 1,
        'unit_price' => 1200,
        'total_amount' => 1200,
        'discount' => 0,
        'payment_status' => 'paid',
        'customer_id' => null,
    ]);

    expect($sale->customer_id)->toBeNull();

    Livewire::actingAs($admin)
        ->test('pages::sales')
        ->call('syncSaleCustomer', $sale->id);

    $sale->refresh();
    expect($sale->customer_id)->not->toBeNull();

    $user = User::find($sale->customer_id);
    expect($user)->not->toBeNull();
    expect($user->role?->name)->toBe(RoleName::Customer->value);
});
