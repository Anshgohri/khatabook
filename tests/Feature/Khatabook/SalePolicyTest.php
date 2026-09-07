<?php

use App\Enums\RoleName;
use App\Models\Sale;
use App\Models\User;

test('every role can view the sales list', function (RoleName $role) {
    $user = User::factory()->role($role)->create();

    expect($user->can('viewAny', Sale::class))->toBeTrue();
})->with(RoleName::cases());

test('only admin, manager, and staff can create sales', function () {
    expect(User::factory()->role(RoleName::Admin)->create()->can('create', Sale::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('create', Sale::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Staff)->create()->can('create', Sale::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Viewer)->create()->can('create', Sale::class))->toBeFalse();
});

test('staff can only view and update their own sale', function () {
    $owner = User::factory()->role(RoleName::Staff)->create();
    $other = User::factory()->role(RoleName::Staff)->create();
    $sale = Sale::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('view', $sale))->toBeTrue();
    expect($owner->can('update', $sale))->toBeTrue();
    expect($other->can('view', $sale))->toBeFalse();
    expect($other->can('update', $sale))->toBeFalse();
});

test('staff can never delete a sale', function () {
    $owner = User::factory()->role(RoleName::Staff)->create();
    $sale = Sale::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('delete', $sale))->toBeFalse();
});

test('manager can view, update, and delete any sale', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $sale = Sale::factory()->create();

    expect($manager->can('view', $sale))->toBeTrue();
    expect($manager->can('update', $sale))->toBeTrue();
    expect($manager->can('delete', $sale))->toBeTrue();
});

test('viewer has read-only access to any sale', function () {
    $viewer = User::factory()->role(RoleName::Viewer)->create();
    $sale = Sale::factory()->create();

    expect($viewer->can('view', $sale))->toBeTrue();
    expect($viewer->can('update', $sale))->toBeFalse();
    expect($viewer->can('delete', $sale))->toBeFalse();
});

test('admin bypasses the policy for any sale', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $sale = Sale::factory()->create();

    expect($admin->can('view', $sale))->toBeTrue();
    expect($admin->can('update', $sale))->toBeTrue();
    expect($admin->can('delete', $sale))->toBeTrue();
});
