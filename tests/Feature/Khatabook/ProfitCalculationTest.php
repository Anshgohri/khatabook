<?php

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('admin can set cost price on product and calculate profit accurately', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    // 1. Create product with cost price (making cost = 240) and unit price (500)
    Livewire::actingAs($admin)
        ->test('pages::products')
        ->set('name', '5 feet Ghodi')
        ->set('unit_price', 500)
        ->set('cost_price', 240)
        ->set('type', 'finished_good')
        ->set('unit', 'pcs')
        ->call('saveProduct')
        ->assertHasNoErrors();

    $product = Product::where('name', '5 feet Ghodi')->first();
    expect($product)->not->toBeNull();
    expect((float) $product->unit_price)->toBe(500.0);
    expect((float) $product->cost_price)->toBe(240.0);

    // 2. Create a sale of 1 unit of 5 feet Ghodi for 500
    Livewire::actingAs($admin)
        ->test('pages::sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Accha Pal')
        ->set('saleItems', [
            ['product_id' => $product->id, 'quantity' => 1, 'unit_price' => 500, 'total_price' => 500],
        ])
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();

    // Total cost = 1 * 240 = 240. Total Amount = 500. Profit = 500 - 240 = 260.
    expect((float) $sale->total_amount)->toBe(500.0);
    expect($sale->totalCost())->toBe(240.0);
    expect($sale->profit())->toBe(260.0);
});

test('profit calculation handles multi-item sales and discounts correctly', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    $prod1 = Product::factory()->create(['name' => 'Ghodi Medium', 'unit_price' => 500, 'cost_price' => 240]);
    $prod2 = Product::factory()->create(['name' => 'Baans Heavy', 'unit_price' => 300, 'cost_price' => 150]);

    // Sale: 2x Ghodi Medium (2*500=1000, cost=2*240=480) + 1x Baans Heavy (1*300=300, cost=1*150=150)
    // Subtotal = 1300, Discount = 100 => Total Amount = 1200. Total Cost = 630. Profit = 1200 - 630 = 570.
    Livewire::actingAs($admin)
        ->test('pages::sales-form')
        ->set('date', now()->toDateString())
        ->set('customer_name', 'Bulk Buyer')
        ->set('saleItems', [
            ['product_id' => $prod1->id, 'quantity' => 2, 'unit_price' => 500, 'total_price' => 1000],
            ['product_id' => $prod2->id, 'quantity' => 1, 'unit_price' => 300, 'total_price' => 300],
        ])
        ->set('discount', 100)
        ->set('payment_status', 'paid')
        ->call('save')
        ->assertHasNoErrors();

    $sale = Sale::latest()->first();

    expect((float) $sale->total_amount)->toBe(1200.0);
    expect($sale->totalCost())->toBe(630.0);
    expect($sale->profit())->toBe(570.0);
});

test('sales page computes daily profit and period profit for admin', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    $prod = Product::factory()->create(['name' => 'Table', 'unit_price' => 1000, 'cost_price' => 600]);

    Sale::factory()->create([
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'total_amount' => 1000,
    ])->syncItemsAndInventory([
        ['product_id' => $prod->id, 'quantity' => 1, 'unit_price' => 1000],
    ]);

    $component = Livewire::actingAs($admin)->test('pages::sales');

    expect($component->get('dailyProfit'))->toBe(400.0);
    expect($component->get('filteredProfit'))->toBe(400.0);
});

test('dashboard computes daily profit metric for admin', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();

    $prod = Product::factory()->create(['unit_price' => 500, 'cost_price' => 240]);

    Sale::factory()->create([
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'total_amount' => 500,
    ])->syncItemsAndInventory([
        ['product_id' => $prod->id, 'quantity' => 1, 'unit_price' => 500],
    ]);

    $response = $this->actingAs($admin)->get(route('dashboard'));

    $response->assertViewHas('profitToday', 260.0);
});
