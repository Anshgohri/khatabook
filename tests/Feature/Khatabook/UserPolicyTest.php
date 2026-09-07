<?php

use App\Enums\RoleName;
use App\Models\User;

test('only admin can invite (create) users', function () {
    expect(User::factory()->role(RoleName::Admin)->create()->can('create', User::class))->toBeTrue();
    expect(User::factory()->role(RoleName::Manager)->create()->can('create', User::class))->toBeFalse();
});

test('manager can view and update staff and viewer accounts', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $staff = User::factory()->role(RoleName::Staff)->create();
    $viewer = User::factory()->role(RoleName::Viewer)->create();

    expect($manager->can('view', $staff))->toBeTrue();
    expect($manager->can('update', $staff))->toBeTrue();
    expect($manager->can('view', $viewer))->toBeTrue();
    expect($manager->can('update', $viewer))->toBeTrue();
});

test('manager cannot view or update other managers or admins', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $otherManager = User::factory()->role(RoleName::Manager)->create();
    $admin = User::factory()->role(RoleName::Admin)->create();

    expect($manager->can('view', $otherManager))->toBeFalse();
    expect($manager->can('update', $otherManager))->toBeFalse();
    expect($manager->can('view', $admin))->toBeFalse();
    expect($manager->can('update', $admin))->toBeFalse();
});

test('staff and viewer cannot view or manage any user accounts', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();
    $target = User::factory()->role(RoleName::Viewer)->create();

    expect($staff->can('viewAny', User::class))->toBeFalse();
    expect($staff->can('view', $target))->toBeFalse();
    expect($staff->can('update', $target))->toBeFalse();
});

test('no one but admin can delete a user', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $target = User::factory()->role(RoleName::Staff)->create();

    expect($manager->can('delete', $target))->toBeFalse();

    $admin = User::factory()->role(RoleName::Admin)->create();
    expect($admin->can('delete', $target))->toBeTrue();
});
