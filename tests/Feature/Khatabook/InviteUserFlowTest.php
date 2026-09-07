<?php

use App\Actions\InviteUser;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\URL;
use Livewire\Livewire;

test('an invited user can set their password and their account becomes active', function () {
    $role = Role::query()->firstOrCreate(['name' => RoleName::Staff->value]);
    $invited = app(InviteUser::class)->invite('New Hire', 'new-hire@example.com', $role);

    expect($invited->status)->toBe('invited');

    Livewire::test('pages::auth.accept-invite', ['user' => $invited])
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('acceptInvite')
        ->assertHasNoErrors();

    expect($invited->fresh()->status)->toBe('active');
    expect(auth()->id())->toBe($invited->id);
});

test('the invite link requires a valid signature', function () {
    $role = Role::query()->firstOrCreate(['name' => RoleName::Staff->value]);
    $invited = app(InviteUser::class)->invite('New Hire', 'new-hire@example.com', $role);

    $this->get(route('invite.accept', $invited))->assertForbidden();
});

test('a valid signed invite link is accessible for an invited user', function () {
    $role = Role::query()->firstOrCreate(['name' => RoleName::Staff->value]);
    $invited = app(InviteUser::class)->invite('New Hire', 'new-hire@example.com', $role);

    $url = URL::temporarySignedRoute('invite.accept', now()->addDays(7), ['user' => $invited->id]);

    $this->get($url)->assertOk();
});

test('an already active user cannot reuse an invite link', function () {
    $user = User::factory()->role(RoleName::Staff)->create(['status' => 'active']);

    $url = URL::temporarySignedRoute('invite.accept', now()->addDays(7), ['user' => $user->id]);

    $this->get($url)->assertNotFound();
});
