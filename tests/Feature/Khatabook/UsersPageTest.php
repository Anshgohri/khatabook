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
    $role = Role::where('name', RoleName::Staff->value)->firstOrFail();

    Livewire::actingAs($admin)
        ->test('pages::khatabook.users')
        ->set('invite_name', 'New Hire')
        ->set('invite_email', 'new-hire@example.com')
        ->set('invite_role_id', (string) $role->id)
        ->call('inviteUser')
        ->assertHasNoErrors();

    $invited = User::where('email', 'new-hire@example.com')->sole();

    expect($invited->status)->toBe('invited');
    expect($invited->role_id)->toBe($role->id);

    Notification::assertSentTo($invited, UserInvited::class);
});

test('manager cannot edit another manager', function () {
    $manager = User::factory()->role(RoleName::Manager)->create();
    $otherManager = User::factory()->role(RoleName::Manager)->create();

    Livewire::actingAs($manager)
        ->test('pages::khatabook.users')
        ->call('editUser', $otherManager->id)
        ->assertForbidden();
});
