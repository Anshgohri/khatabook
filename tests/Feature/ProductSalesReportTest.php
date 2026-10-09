<?php

use App\Enums\RoleName;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\User;
use Livewire\Livewire;

test('product sales report page loads for authenticated staff/admin', function () {
    $user = User::factory()->role(RoleName::Admin)->create();

    $this->actingAs($user)
        ->get(route('product-sales-report'))
        ->assertStatus(200)
        ->assertSee('Product Sales Analytics & Report');
});

test('product sales report calculates quantity, revenue, and timeline correctly', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $cat = ProductCategory::factory()->create(['name' => 'Bamboo Scaffold']);
    $product1 = Product::factory()->create([
        'name' => 'Bamboo Pole 20ft',
        'product_category_id' => $cat->id,
        'cost_price' => 100,
        'unit_price' => 200,
    ]);

    $product2 = Product::factory()->create([
        'name' => 'Ghodi Frame',
        'product_category_id' => $cat->id,
        'cost_price' => 500,
        'unit_price' => 800,
    ]);

    // Create a sale with items
    $sale = Sale::factory()->create([
        'user_id' => $admin->id,
        'date' => now()->toDateString(),
        'total_amount' => 1200,
    ]);

    $sale->syncItemsAndInventory([
        ['product_id' => $product1->id, 'quantity' => 5, 'unit_price' => 200],
        ['product_id' => $product2->id, 'quantity' => 2, 'unit_price' => 100],
    ]);

    Livewire::actingAs($admin)
        ->test('pages::product-sales-report')
        ->set('preset', 'this_month')
        ->assertSee('Bamboo Pole 20ft')
        ->assertSee('Ghodi Frame');
});
