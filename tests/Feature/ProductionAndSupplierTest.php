<?php

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductionLog;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Models\User;

test('purchasing raw material from supplier updates supplier balance correctly', function () {
    $user = User::factory()->create();

    $supplier = Supplier::factory()->create([
        'user_id' => $user->id,
        'name' => 'Raja Assam',
        'location' => 'Assam',
        'material_supplied' => 'Bans (Bamboo)',
    ]);

    // Raw material purchase invoice of 40,000
    SupplierPayment::create([
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'raw_material_purchase',
        'amount' => 40000.00,
        'payment_method' => 'bank_transfer',
        'notes' => 'Purchased Bans batch from Assam',
    ]);

    // Payment made of 15,000 to Raja
    SupplierPayment::create([
        'supplier_id' => $supplier->id,
        'user_id' => $user->id,
        'date' => now()->toDateString(),
        'type' => 'payment_made',
        'amount' => 15000.00,
        'payment_method' => 'upi',
    ]);

    $supplier->refresh();

    // Outstanding balance owed to supplier = 40,000 - 15,000 = 25,000
    expect((float) $supplier->outstanding_balance)->toEqual(25000.00);
});

test('production log updates finished product stock, reduces raw material stock and logs worker wage', function () {
    $user = User::factory()->create();
    $category = ProductCategory::factory()->create();

    // Raw Material: Bans (Stock: 100)
    $rawMaterial = Product::create([
        'product_category_id' => $category->id,
        'type' => 'raw_material',
        'name' => 'Bans (Bamboo)',
        'unit_price' => 150.00,
        'unit' => 'bundle',
        'stock_level' => 100,
    ]);

    // Finished Good: Bamboo Stool (Stock: 5)
    $finishedProduct = Product::create([
        'product_category_id' => $category->id,
        'type' => 'finished_good',
        'name' => 'Bamboo Stool',
        'unit_price' => 600.00,
        'unit' => 'pcs',
        'stock_level' => 5,
    ]);

    // Employee Worker: Talib
    $worker = Employee::factory()->create([
        'user_id' => $user->id,
        'name' => 'Talib',
        'default_daily_rate' => 500.00,
    ]);

    // Log Production: Talib makes 10 Bamboo Stools, consuming 15 Bans, wage ₹500
    ProductionLog::create([
        'user_id' => $user->id,
        'employee_id' => $worker->id,
        'finished_product_id' => $finishedProduct->id,
        'quantity_produced' => 10,
        'raw_material_id' => $rawMaterial->id,
        'raw_material_consumed_qty' => 15,
        'worker_wage' => 500.00,
        'date' => now()->toDateString(),
        'notes' => 'Evening production batch',
    ]);

    $finishedProduct->refresh();
    $rawMaterial->refresh();
    $worker->refresh();

    // Finished product stock should be 5 + 10 = 15
    expect($finishedProduct->stock_level)->toEqual(15);

    // Raw material stock should be 100 - 15 = 85
    expect($rawMaterial->stock_level)->toEqual(85);

    // Worker Talib should have a daily pay entry of 500 logged
    expect($worker->payments)->toHaveCount(1);
    expect($worker->payments->first()->type)->toBe('daily_pay');
    expect((float) $worker->payments->first()->amount)->toEqual(500.00);
});

test('finished products dropdown filters products where category is finished', function () {
    $finishedCategory = ProductCategory::create(['name' => 'Finished Goods']);
    $rawCategory = ProductCategory::create(['name' => 'Raw Materials']);

    $finishedProduct = Product::create([
        'product_category_id' => $finishedCategory->id,
        'name' => 'Finished Chair',
        'unit_price' => 500,
        'unit' => 'pcs',
        'stock_level' => 10,
    ]);

    $rawMaterial = Product::create([
        'product_category_id' => $rawCategory->id,
        'name' => 'Raw Timber',
        'unit_price' => 200,
        'unit' => 'pcs',
        'stock_level' => 50,
    ]);

    $finishedProducts = Product::finished()->get();
    $rawMaterials = Product::rawMaterial()->get();

    expect($finishedProducts->pluck('id'))->toContain($finishedProduct->id);
    expect($finishedProducts->pluck('id'))->not()->toContain($rawMaterial->id);

    expect($rawMaterials->pluck('id'))->toContain($rawMaterial->id);
    expect($rawMaterials->pluck('id'))->not()->toContain($finishedProduct->id);
});
