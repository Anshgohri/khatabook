<?php

use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use App\Notifications\UserInvited;
use Illuminate\Support\Facades\Notification;
use Livewire\Livewire;

test('staff cannot access the users page', function () {
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($staff)
        ->test('pages::khatabook.users')
        ->assertForbidden();
});

test('admin can invite a user and the invitation notification is sent', function () {
    Notification::fake();

    $admin = User::factory()->role(RoleName::Admin)->create();
    $role = Role::query()->firstOrCreate(['name' => RoleName::Staff->value]);

    Livewire::actingAs($admin)
        ->test('pages::khatabook.users')
        ->set('invite_name', 'New Hire')
        ->set('invite_email', 'new-hire@example.com')
        ->set('invite_phone', '9876543210')
        ->set('invite_city', 'Jaipur')
        ->set('invite_address', '123 Station Road')
        ->set('invite_role_id', (string) $role->id)
        ->call('inviteUser')
        ->assertHasNoErrors();

    $invited = User::where('email', 'new-hire@example.com')->sole();

    expect($invited->status)->toBe('invited');
    expect($invited->role_id)->toBe($role->id);
    expect($invited->phone)->toBe('9876543210');
    expect($invited->city)->toBe('Jaipur');
    expect($invited->address)->toBe('123 Station Road');

    Notification::assertSentTo($invited, UserInvited::class);
});

test('phone number must contain only numeric digits', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $role = Role::query()->firstOrCreate(['name' => RoleName::Staff->value]);

    Livewire::actingAs($admin)
        ->test('pages::khatabook.users')
        ->set('invite_name', 'Invalid Phone Hire')
        ->set('invite_email', 'invalid-phone@example.com')
        ->set('invite_phone', '98765-ABCDE')
        ->set('invite_role_id', (string) $role->id)
        ->call('inviteUser')
        ->assertHasErrors(['invite_phone']);
});

test('manager cannot edit another manager', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $otherManager = User::factory()->role(RoleName::Manager)->create();

    Livewire::actingAs($manager)
        ->test('pages::khatabook.users')
        ->call('editUser', $otherManager->id)
        ->assertForbidden();
});

test('admin can delete a user', function () {
    $admin = User::factory()->role(RoleName::Admin)->create();
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($admin)
        ->test('pages::khatabook.users')
        ->call('deleteUser', $staff->id)
        ->assertHasNoErrors();

    expect(User::find($staff->id))->toBeNull();
});

test('manager cannot delete a user', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $staff = User::factory()->role(RoleName::Staff)->create();

    Livewire::actingAs($manager)
        ->test('pages::khatabook.users')
        ->call('deleteUser', $staff->id)
        ->assertForbidden();

    expect(User::find($staff->id))->not->toBeNull();
});
