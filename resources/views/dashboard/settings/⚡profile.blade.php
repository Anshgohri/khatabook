<?php

use App\Concerns\ProfileValidationRules;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Profile settings')] class extends Component {
    use ProfileValidationRules, WithFileUploads;

    public string $name = '';
    public string $email = '';
    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate($this->profileRules($user->id));

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        Flux::toast(variant: 'success', text: __('Profile updated.'));
    }

    /**
     * Upload or replace the profile avatar.
     */
    public function uploadAvatar(): void
    {
        $this->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ]);

        $user = Auth::user();

        // Delete old avatar
        if ($user->avatar_path && Storage::exists($user->avatar_path)) {
            Storage::delete($user->avatar_path);
        }

        $path = $this->avatar->store('avatars', 'public');
        $user->update(['avatar_path' => $path]);
        $this->avatar = null;

        Flux::toast(variant: 'success', text: __('Profile photo updated.'));
    }

    /**
     * Remove the current profile avatar.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        if ($user->avatar_path && Storage::exists($user->avatar_path)) {
            Storage::delete($user->avatar_path);
        }

        $user->update(['avatar_path' => null]);
        $this->avatar = null;

        Flux::toast(variant: 'success', text: __('Profile photo removed.'));
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }

    #[Computed]
    public function hasUnverifiedEmail(): bool
    {
        return Auth::user() instanceof MustVerifyEmail && ! Auth::user()->hasVerifiedEmail();
    }

    #[Computed]
    public function showDeleteUser(): bool
    {
        return ! Auth::user() instanceof MustVerifyEmail
            || (Auth::user() instanceof MustVerifyEmail && Auth::user()->hasVerifiedEmail());
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading level="2" class="sr-only">{{ __('Profile settings') }}</flux:heading>

    <x-pages::settings.layout :heading="__('Profile')" :subheading="__('Update your name, email address and profile photo')">

        {{-- Profile Photo Section --}}
        <div class="my-6 flex flex-col gap-4">
            <flux:heading size="sm">{{ __('Profile Photo') }}</flux:heading>

            <div class="flex items-center gap-5">
                {{-- Current avatar preview --}}
                <div class="relative group">
                    @if (auth()->user()->avatar_path)
                        <img src="{{ Storage::url(auth()->user()->avatar_path) }}"
                             alt="{{ auth()->user()->name }}"
                             class="w-20 h-20 rounded-full object-cover ring-2 ring-zinc-200 dark:ring-zinc-700 shadow" />
                        <button wire:click="removeAvatar"
                                wire:confirm="{{ __('Remove your profile photo?') }}"
                                class="absolute -top-1 -right-1 bg-red-500 hover:bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity shadow"
                                title="{{ __('Remove photo') }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    @else
                        <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 flex items-center justify-center text-white text-2xl font-bold shadow ring-2 ring-zinc-200 dark:ring-zinc-700">
                            {{ auth()->user()->initials() }}
                        </div>
                    @endif
                </div>

                <div class="flex flex-col gap-2">
                    <flux:text size="sm" class="text-zinc-600 dark:text-zinc-400">
                        {{ __('JPG, PNG, WEBP or GIF — max 5 MB') }}
                    </flux:text>

                    <form wire:submit="uploadAvatar" class="flex items-end gap-2">
                        <div>
                            <flux:input type="file" wire:model="avatar" accept="image/*" />
                            @error('avatar')
                                <span class="text-xs text-red-500 mt-1 block">{{ $message }}</span>
                            @enderror
                        </div>
                        <flux:button type="submit" variant="primary" size="sm">
                            {{ __('Upload') }}
                        </flux:button>
                    </form>

                    @if (auth()->user()->avatar_path)
                    <flux:button wire:click="removeAvatar"
                                 wire:confirm="{{ __('Remove your profile photo?') }}"
                                 variant="ghost" size="sm" class="w-fit text-red-500 hover:text-red-600 dark:text-red-400">
                        {{ __('Remove photo') }}
                    </flux:button>
                    @endif
                </div>
            </div>
        </div>

        <flux:separator />

        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if ($this->hasUnverifiedEmail)
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full" data-test="update-profile-button">
                        {{ __('Save') }}
                    </flux:button>
                </div>

            </div>
        </form>

        @if ($this->showDeleteUser)
            <livewire:pages::settings.delete-user-form />
        @endif
    </x-pages::settings.layout>
</section>
