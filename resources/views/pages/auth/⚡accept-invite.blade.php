<?php

use App\Concerns\PasswordValidationRules;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Layout('layouts::auth')] #[Title('Accept invitation')] class extends Component {
    use PasswordValidationRules;

    public User $user;

    public string $password = '';

    public string $password_confirmation = '';

    public function mount(User $user): void
    {
        abort_unless($user->status === 'invited', 404);

        $this->user = $user;
    }

    public function acceptInvite(): void
    {
        $validated = $this->validate([
            'password' => $this->passwordRules(),
        ]);

        $this->user->forceFill([
            'password' => Hash::make($validated['password']),
            'status' => 'active',
            'email_verified_at' => now(),
        ])->save();

        Auth::login($this->user);

        $this->redirectIntended(default: route('dashboard', absolute: false));
    }
}; ?>

<div class="flex flex-col gap-6">
    <x-auth-header :title="__('Set your password')" :description="__('Welcome to Bamboo Khatabook. Choose a password to activate your account.')" />

    <form wire:submit="acceptInvite" class="flex flex-col gap-6">
        <flux:input
            wire:model="password"
            :label="__('Password')"
            type="password"
            required
            autofocus
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
            {{ __('Activate account') }}
        </flux:button>
    </form>
</div>
