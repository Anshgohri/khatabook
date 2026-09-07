<?php

use App\Enums\RoleName;
use App\Models\InventoryLog;
use App\Models\Product;
use App\Models\User;

test('every role can view products and inventory logs', function (RoleName $role) {
    $user = User::factory()->role($role)->create();

    expect($user->can('viewAny', Product::class))->toBeTrue();
    expect($user->can('viewAny', InventoryLog::class))->toBeTrue();
})->with(RoleName::cases());

test('only admin and manager can manage products', function () {
    $product = Product::factory()->create();

    expect(User::factory()->role(RoleName::Manager)->create()->can('create', Product::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('update', $product))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('delete', $product))->toBeTrue();

    expect(User::factory()->role(RoleName::Staff)->create()->can('create', Product::class))->toBeFalse();
    expect(User::factory()->role(RoleName::Staff)->create()->can('update', $product))->toBeFalse();
    expect(User::factory()->role(RoleName::Viewer)->create()->can('delete', $product))->toBeFalse();
});

test('only admin and manager can adjust inventory', function () {
    expect(User::factory()->role(RoleName::Manager)->create()->can('create', InventoryLog::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Staff)->create()->can('create', InventoryLog::class))->toBeFalse();
    expect(User::factory()->role(RoleName::Viewer)->create()->can('create', InventoryLog::class))->toBeFalse();
});
