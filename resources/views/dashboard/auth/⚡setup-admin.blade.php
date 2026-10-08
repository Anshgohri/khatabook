<?php

use App\Concerns\PasswordValidationRules;
use App\Concerns\ProfileValidationRules;
use App\Enums\RoleName;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Set up admin account')] class extends Component {
    use PasswordValidationRules, ProfileValidationRules;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(): void
    {
        abort_unless(User::query()->doesntExist(), 404);
    }

    public function createAdmin(): void
    {
        abort_unless(User::query()->doesntExist(), 404);

        $validated = $this->validate([
            ...$this->profileRules(),
            'password' => $this->passwordRules(),
        ]);

        $adminRole = Role::query()->firstOrCreate(
            ['name' => RoleName::Admin->value],
            ['description' => 'Full access to all data and settings.'],
        );

        $admin = User::forceCreate([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role_id' => $adminRole->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        Auth::login($admin);

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Set up your admin account')" :description="__('No users exist yet. Create the first account, which will be the system Admin.')" />

    <form wire:submit="createAdmin" class="flex flex-col gap-6">
        <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

        <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Password')"
            passwordrules="{{ \Illuminate\Validation\Rules\Password::defaults()->toPasswordRulesString() }}"
            viewable
        />

        <flux:input
            wire:model="password_confirmation"
            :label="__('Confirm password')"
            type="password"
            required
            autocomplete="new-password"
            :placeholder="__('Confirm password')"
            viewable
        />

        <flux:button type="submit" variant="primary" class="w-full">
            {{ __('Create admin account') }}
        </flux:button>
    </form>
</div>
