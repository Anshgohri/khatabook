<?php

use App\Models\Setting;
use Flux\Flux;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Store Settings')] class extends Component {
    public string $storeName = '';

    public string $storeSubtitle = '';

    public string $storeAddress = '';

    public string $storePhone = '';

    public string $storeEmail = '';

    public string $storeTerms = '';

    public string $invoicePrefix = 'INV-';

    public function mount(): void
    {
        $details = Setting::getStoreDetails();

        $this->storeName = $details['storeName'];
        $this->storeSubtitle = $details['storeSubtitle'];
        $this->storeAddress = $details['storeAddress'];
        $this->storePhone = $details['storePhone'];
        $this->storeEmail = $details['storeEmail'];
        $this->storeTerms = $details['storeTerms'];
        $this->invoicePrefix = $details['invoicePrefix'];
    }

    public function saveSettings(): void
    {
        if (! auth()->user()?->isAdmin()) {
            Flux::toast(variant: 'danger', text: __('Only Administrators can update store settings.'));
            return;
        }

        $validated = $this->validate([
            'storeName' => ['required', 'string', 'max:255'],
            'storeSubtitle' => ['nullable', 'string', 'max:255'],
            'storeAddress' => ['required', 'string', 'max:500'],
            'storePhone' => ['required', 'string', 'max:255'],
            'storeEmail' => ['required', 'email', 'max:255'],
            'storeTerms' => ['nullable', 'string', 'max:2000'],
            'invoicePrefix' => ['required', 'string', 'max:20'],
        ]);

        Setting::set('store_name', $validated['storeName']);
        Setting::set('store_subtitle', $validated['storeSubtitle']);
        Setting::set('store_address', $validated['storeAddress']);
        Setting::set('store_phone', $validated['storePhone']);
        Setting::set('store_email', $validated['storeEmail']);
        Setting::set('store_terms', $validated['storeTerms']);
        Setting::set('invoice_prefix', strtoupper($validated['invoicePrefix']));

        Flux::toast(variant: 'success', text: __('Store credentials & invoice settings saved successfully!'));
    }

    public function resetDefaults(): void
    {
        if (! auth()->user()?->isAdmin()) {
            Flux::toast(variant: 'danger', text: __('Only Administrators can reset store settings.'));
            return;
        }

        Setting::set('store_name', config('khatabook.store_name'));
        Setting::set('store_subtitle', config('khatabook.store_subtitle'));
        Setting::set('store_address', config('khatabook.store_address'));
        Setting::set('store_phone', config('khatabook.store_phone'));
        Setting::set('store_email', config('khatabook.store_email'));
        Setting::set('store_terms', config('khatabook.store_terms'));
        Setting::set('invoice_prefix', config('khatabook.invoice_prefix'));

        $this->mount();

        Flux::toast(variant: 'info', text: __('Store settings reset to default system parameters.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Store & Invoice Settings') }}</flux:heading>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/40">
                    ⚙️ {{ __('Global Credentials') }}
                </span>
            </div>
            <flux:subheading>{{ __('Manage global store profile, contact numbers, email address, and invoice headers in one place.') }}</flux:subheading>
        </div>

        @if (auth()->user()?->isAdmin())
            <div class="flex items-center gap-2">
                <flux:button variant="ghost" icon="arrow-path" wire:click="resetDefaults" wire:confirm="{{ __('Reset all store credentials back to system defaults?') }}">
                    {{ __('Reset Defaults') }}
                </flux:button>
                <flux:button variant="primary" icon="check" wire:click="saveSettings">
                    {{ __('Save All Credentials') }}
                </flux:button>
            </div>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <!-- Settings Form -->
        <div class="lg:col-span-2 flex flex-col gap-6">
            <form wire:submit="saveSettings" class="flex flex-col gap-6">
                <!-- Store Profile Section -->
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col gap-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                        <span class="text-xl">🏪</span>
                        <h3 class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">{{ __('Store Identity & Branding') }}</h3>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="storeName" :label="__('Store Name')" placeholder="Ashok Kumar Baans Store" required />
                        <flux:input wire:model="invoicePrefix" :label="__('Invoice Number Prefix')" placeholder="INV-" required />
                    </div>

                    <flux:input wire:model="storeSubtitle" :label="__('Tagline / Subtitle')" placeholder="Direct Timber Merchant • Raw Bamboo, Ghodi, Chaali & Siddhi" />
                </div>

                <!-- Contact Info Section -->
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col gap-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                        <span class="text-xl">📞</span>
                        <h3 class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">{{ __('Contact Details & Address') }}</h3>
                    </div>

                    <flux:textarea wire:model="storeAddress" :label="__('Physical Store Address')" placeholder="House No 2755, Opposite Gaushala Road, Janak Puri, Karnal, Haryana - 132001" rows="2" required />

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="storePhone" :label="__('Official Phone Numbers')" placeholder="Ashok Kumar: 9254998000, 9255523276 | Ansh: 8950304888" required />
                        <flux:input wire:model="storeEmail" :label="__('Official Email Address')" placeholder="anshgohri8950@gmail.com" type="email" required />
                    </div>
                </div>

                <!-- Invoice Terms & Conditions -->
                <div class="bg-white dark:bg-zinc-900 p-6 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col gap-4">
                    <div class="flex items-center gap-2 pb-2 border-b border-zinc-200 dark:border-zinc-800">
                        <span class="text-xl">📜</span>
                        <h3 class="font-extrabold text-base text-zinc-900 dark:text-zinc-100">{{ __('Invoice Terms & Conditions') }}</h3>
                    </div>

                    <flux:textarea wire:model="storeTerms" :label="__('Terms & Conditions (Printed at bottom of invoices)')" rows="4" placeholder="1. Goods once sold are strictly governed under timber yard standard policies.&#10;2. Raw bamboo poles & Ghodi trestles are checked before dispatch." />
                </div>

                @if (auth()->user()?->isAdmin())
                    <div class="flex justify-end gap-3">
                        <flux:button type="submit" variant="primary" icon="check" class="px-6 py-2.5">
                            {{ __('Save All Credentials') }}
                        </flux:button>
                    </div>
                @endif
            </form>
        </div>

        <!-- Live Preview Card -->
        <div class="lg:col-span-1">
            <div class="sticky top-6 bg-slate-950 text-white p-6 rounded-3xl border border-emerald-500/30 shadow-2xl flex flex-col gap-5">
                <div class="flex items-center justify-between pb-3 border-b border-slate-800">
                    <span class="text-xs font-black uppercase text-emerald-400 tracking-wider">👁️ Live Invoice Banner Preview</span>
                    <span class="px-2 py-0.5 rounded text-[10px] font-bold bg-slate-800 text-slate-300">Live View</span>
                </div>

                <!-- Banner Preview Container -->
                <div class="bg-emerald-950 text-white p-4 rounded-xl border border-emerald-800 flex flex-col gap-3 shadow-inner">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 shrink-0 bg-slate-900 rounded-lg border border-emerald-500 p-1 flex items-center justify-center">
                            <img src="{{ asset('images/ak-emblem.png') }}" class="w-full h-full object-contain" alt="AK Emblem">
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="text-sm font-black text-amber-300 truncate uppercase">
                                {{ $storeName ?: 'Store Name' }}
                            </h4>
                            <p class="text-[10px] font-bold text-emerald-400 truncate uppercase">
                                {{ $storeSubtitle ?: 'Store Subtitle' }}
                            </p>
                        </div>
                    </div>

                    <div class="text-[10px] text-slate-300 leading-tight space-y-1 border-t border-emerald-900/60 pt-2">
                        <p class="truncate"><span class="text-slate-400">Address:</span> {{ $storeAddress }}</p>
                        <p class="truncate"><span class="text-slate-400">Phone:</span> {{ $storePhone }}</p>
                        <p class="truncate"><span class="text-slate-400">Email:</span> {{ $storeEmail }}</p>
                    </div>

                    <div class="pt-2 border-t border-emerald-900/60 flex items-center justify-between text-[11px]">
                        <span class="font-bold text-white">Sample Invoice:</span>
                        <span class="font-extrabold text-emerald-400">{{ strtoupper($invoicePrefix) }}00015</span>
                    </div>
                </div>

                <div class="bg-slate-900 p-4 rounded-xl border border-slate-800 text-[11px] text-slate-400 leading-relaxed">
                    💡 <strong class="text-slate-200">Centralized Credentials:</strong> Updating these fields instantly reflects on all generated PDF invoices, print dialogs, public headers, and official sales records without editing code files.
                </div>
            </div>
        </div>
    </div>
</div>
