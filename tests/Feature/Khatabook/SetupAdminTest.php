<?php

use App\Enums\RoleName;
use App\Models\User;
use Livewire\Livewire;

test('the setup page is accessible when no users exist', function () {
    $this->get(route('setup'))->assertOk();
});

test('the setup page is unavailable once a user already exists', function () {
    User::factory()->create();

    $this->get(route('setup'))->assertNotFound();
});

test('submitting the setup form creates an admin and logs them in', function () {
    Livewire::test('pages::auth.setup-admin')
        ->set('name', 'First Admin')
        ->set('email', 'admin@example.com')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('createAdmin')
        ->assertHasNoErrors();

    $admin = User::sole();

    expect($admin->email)->toBe('admin@example.com');
    expect($admin->isAdmin())->toBeTrue();
    expect($admin->status)->toBe('active');
    expect($admin->hasVerifiedEmail())->toBeTrue();
    expect(auth()->id())->toBe($admin->id);
});

test('a freshly created admin can reach the dashboard without being asked to verify their email', function () {
    Livewire::test('pages::auth.setup-admin')
        ->set('name', 'First Admin')
        ->set('email', 'admin@example.com')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password')
        ->call('createAdmin');

    $this->get(route('dashboard'))->assertOk();
});

test('the setup form refuses to create a second admin if a user was created in the meantime', function () {
    $component = Livewire::test('pages::auth.setup-admin')
        ->set('name', 'Late Admin')
        ->set('email', 'late-admin@example.com')
        ->set('password', 'a-strong-password')
        ->set('password_confirmation', 'a-strong-password');

    User::factory()->create();

    $component->call('createAdmin')->assertNotFound();

    expect(User::where('email', 'late-admin@example.com')->exists())->toBeFalse();
});

test('the login page offers to set up an admin account only when no users exist', function () {
    $this->get(route('login'))->assertSee(__('Set up admin account'));

    User::factory()->role(RoleName::Admin)->create();

    $this->get(route('login'))->assertDontSee(__('Set up admin account'));
});
