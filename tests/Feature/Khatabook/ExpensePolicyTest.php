<?php

use App\Enums\RoleName;
use App\Models\Expense;
use App\Models\User;

test('only admin, manager, and staff can create expenses', function () {
    expect(User::factory()->role(RoleName::Admin)->create()->can('create', Expense::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('create', Expense::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Staff)->create()->can('create', Expense::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Viewer)->create()->can('create', Expense::class))->toBeFalse();
});

test('staff can only view and update their own expense', function () {
    $owner = User::factory()->role(RoleName::Staff)->create();
    $other = User::factory()->role(RoleName::Staff)->create();
    $expense = Expense::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('view', $expense))->toBeTrue();
    expect($owner->can('update', $expense))->toBeTrue();
    expect($other->can('view', $expense))->toBeFalse();
    expect($other->can('update', $expense))->toBeFalse();
});

test('staff can never delete an expense', function () {
    $owner = User::factory()->role(RoleName::Staff)->create();
    $expense = Expense::factory()->create(['user_id' => $owner->id]);

    expect($owner->can('delete', $expense))->toBeFalse();
});

test('manager can view, update, and delete any expense', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $expense = Expense::factory()->create();

    expect($manager->can('view', $expense))->toBeTrue();
    expect($manager->can('update', $expense))->toBeTrue();
    expect($manager->can('delete', $expense))->toBeTrue();
});

test('viewer has read-only access to any expense', function () {
    $viewer = User::factory()->role(RoleName::Viewer)->create();
    $expense = Expense::factory()->create();

    expect($viewer->can('view', $expense))->toBeTrue();
    expect($viewer->can('update', $expense))->toBeFalse();
    expect($viewer->can('delete', $expense))->toBeFalse();
});
