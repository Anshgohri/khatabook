<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;

test('customer logging in to dashboard gets redirected to my-orders', function () {
    $customer = User::factory()->role(RoleName::Customer)->create();

    $this->actingAs($customer)
        ->get(route('dashboard'))
        ->assertRedirect(route('my-orders'));
});

test('customer can access my-orders, products, and profile settings pages', function () {
    $customer = User::factory()->role(RoleName::Customer)->create();

    $this->actingAs($customer)
        ->get(route('my-orders'))
        ->assertOk();

    $this->actingAs($customer)
        ->get(route('products'))
        ->assertOk();

    $this->actingAs($customer)
        ->get(route('profile.edit'))
        ->assertOk();
});

test('customer can view their own sales invoice PDF', function () {
    $customer = User::factory()->role(RoleName::Customer)->create();
    $sale = Sale::factory()->create([
        'customer_id' => $customer->id,
    ]);

    $this->actingAs($customer)
        ->get(route('invoices.sale.view', $sale))
        ->assertOk();
});

test('customer cannot view another customers sales invoice PDF', function () {
    $customer1 = User::factory()->role(RoleName::Customer)->create();
    $customer2 = User::factory()->role(RoleName::Customer)->create();

    $sale = Sale::factory()->create([
        'customer_id' => $customer1->id,
    ]);

    $this->actingAs($customer2)
        ->get(route('invoices.sale.view', $sale))
        ->assertForbidden();
});

test('customer cannot access unauthorized admin/staff pages', function () {
    $customer = User::factory()->role(RoleName::Customer)->create();

    $this->actingAs($customer)->get(route('sales'))->assertForbidden();
    $this->actingAs($customer)->get(route('expenses'))->assertForbidden();
    $this->actingAs($customer)->get(route('users'))->assertForbidden();
    $this->actingAs($customer)->get(route('audit-log'))->assertForbidden();
});
