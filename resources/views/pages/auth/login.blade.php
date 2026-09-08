<x-layouts::auth :title="__('Log in')">
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Log in to your account')" :description="__('Enter your email and password below to log in')" />

        @if (\App\Models\User::query()->doesntExist())
            <flux:callout variant="secondary" icon="sparkles" :heading="__('No accounts exist yet')">
                <x-slot name="actions">
                    <flux:button :href="route('setup')" variant="primary" wire:navigate>{{ __('Set up admin account') }}</flux:button>
                </x-slot>
            </flux:callout>
        @endif

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <x-passkey-verify />

        <form method="POST" action="{{ route('login.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
                :value="old('email')"
                type="email"
                required
                autofocus
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Password -->
            <div class="relative">
                <flux:input
                    name="password"
                    :label="__('Password')"
                    type="password"
                    required
                    autocomplete="current-password"
                    :placeholder="__('Password')"
                    viewable
                />

                @if (Route::has('password.request'))
                    <flux:link class="absolute top-0 text-sm end-0 text-emerald-400 hover:text-emerald-300" :href="route('password.request')" wire:navigate>
                        {{ __('Forgot password?') }}
                    </flux:link>
                @endif
            </div>

            <!-- Remember Me -->
            <flux:checkbox name="remember" :label="__('Remember me')" :checked="old('remember')" />

            <div class="flex items-center justify-end">
                <flux:button variant="primary" type="submit" class="w-full" data-test="login-button">
                    {{ __('Log in') }}
                </flux:button>
            </div>
        </form>

        @if (Route::has('register'))
            <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-slate-300 font-semibold pt-4 border-t border-slate-800">
                <span>{{ __("Don't have an account?") }}</span>
                <flux:link :href="route('register')" wire:navigate class="text-emerald-400 font-black hover:underline">{{ __('Register here') }}</flux:link>
            </div>
        @endif
    </div>
</x-layouts::auth>
