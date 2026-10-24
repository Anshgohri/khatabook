<?php

use App\Models\Supplier;
use App\Models\SupplierPayment;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Supplier Transaction Details')] class extends Component {
    public Supplier $supplier;
    public SupplierPayment $payment;

    public function mount(Supplier $supplier, SupplierPayment $payment): void
    {
        if ($payment->supplier_id !== $supplier->id) {
            $supplier = $payment->supplier;
        }

        $this->authorize('view', $supplier);
        $this->supplier = $supplier;
        $this->payment = $payment;
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <!-- Top Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('suppliers.show', $this->supplier->id)" wire:navigate>
                {{ __('Back to Ledger') }}
            </flux:button>
            <div>
                <flux:heading size="xl">{{ __('Transaction Details') }}</flux:heading>
                <flux:subheading size="sm">{{ __('ID: #:id', ['id' => $this->payment->id]) }} • {{ $this->payment->date->format('d F Y') }}</flux:subheading>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $this->supplier)
            <flux:button variant="primary" icon="pencil" :href="route('suppliers.payments.edit', ['supplier' => $this->supplier->id, 'payment' => $this->payment->id])" wire:navigate>
                {{ __('Edit Entry') }}
            </flux:button>
            @endcan
        </div>
    </div>

    <!-- Main Voucher / Entry Card -->
    <flux:card class="flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-col gap-2">
                <div>
                    @if ($this->payment->type === 'raw_material_purchase')
                        <flux:badge color="orange" size="lg">📦 {{ __('Raw Material Purchase (Invoice)') }}</flux:badge>
                    @else
                        <flux:badge color="green" size="lg">💸 {{ __('Payment Made to Supplier') }}</flux:badge>
                    @endif
                </div>

                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-4xl font-extrabold tracking-tight {{ $this->payment->type === 'raw_material_purchase' ? 'text-orange-600 dark:text-orange-400' : 'text-green-600 dark:text-green-400' }}">
                        {{ $this->payment->type === 'raw_material_purchase' ? '+' : '-' }}₹{{ number_format((float) $this->payment->amount, 2) }}
                    </span>
                </div>

                <flux:text size="sm" class="text-zinc-500">
                    {{ $this->payment->type === 'raw_material_purchase' ? __('Increases outstanding balance owed to supplier') : __('Reduces outstanding balance owed to supplier') }}
                </flux:text>
            </div>

            <div class="sm:text-right flex flex-col gap-1">
                <flux:text size="xs">{{ __('Transaction Date') }}</flux:text>
                <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->payment->date->format('d M Y') }}</span>
                <span class="text-xs text-zinc-400">{{ $this->payment->date->diffForHumans() }}</span>
            </div>
        </div>

        <!-- Metadata Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
            <div>
                <flux:text size="xs">{{ __('Payment Method') }}</flux:text>
                <div class="mt-1 font-semibold uppercase text-zinc-800 dark:text-zinc-200 text-sm">
                    {{ str_replace('_', ' ', $this->payment->payment_method) }}
                </div>
            </div>

            <div>
                <flux:text size="xs">{{ __('Recorded By') }}</flux:text>
                <div class="mt-1 font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                    {{ $this->payment->user?->name ?? '—' }}
                </div>
            </div>

            <div>
                <flux:text size="xs">{{ __('Created At') }}</flux:text>
                <div class="mt-1 font-medium text-zinc-600 dark:text-zinc-400 text-sm">
                    {{ $this->payment->created_at->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Supplier Information Card -->
    <flux:card class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:heading size="md">{{ __('Supplier Information') }}</flux:heading>
            <flux:button size="xs" variant="subtle" icon="arrow-top-right-on-square" :href="route('suppliers.show', $this->supplier->id)" wire:navigate>
                {{ __('Open Ledger') }}
            </flux:button>
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700">
            <div>
                <div class="font-bold text-zinc-900 dark:text-zinc-100 text-base">
                    {{ $this->supplier->name }}
                </div>
                <div class="text-xs text-zinc-500 mt-1 flex items-center gap-2 flex-wrap">
                    @if ($this->supplier->material_supplied)
                        <flux:badge color="blue" size="xs">{{ $this->supplier->material_supplied }}</flux:badge>
                    @endif
                    @if ($this->supplier->location)
                        <span>📍 {{ $this->supplier->location }}</span>
                    @endif
                    @if ($this->supplier->phone)
                        <span>📞 {{ $this->supplier->phone }}</span>
                    @endif
                </div>
            </div>

            <div class="sm:text-right border-t sm:border-t-0 pt-2 sm:pt-0 border-zinc-200 dark:border-zinc-700">
                <flux:text size="xs">{{ __('Current Balance Owed') }}</flux:text>
                <div>
                    @if ((float) $this->supplier->outstanding_balance > 0)
                        <flux:badge color="red" size="md">₹{{ number_format((float) $this->supplier->outstanding_balance, 2) }} due</flux:badge>
                    @else
                        <flux:badge color="zinc" size="md">₹0.00</flux:badge>
                    @endif
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Notes & Item Details Card -->
    <flux:card class="flex flex-col gap-2">
        <flux:heading size="md">{{ __('Notes / Item Details') }}</flux:heading>
        @if ($this->payment->notes)
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 text-sm whitespace-pre-wrap leading-relaxed">
                {{ $this->payment->notes }}
            </div>
        @else
            <flux:text class="text-zinc-400 italic text-sm">{{ __('No additional notes or item details were recorded for this entry.') }}</flux:text>
        @endif
    </flux:card>

    <!-- Bill / Receipt Document Card -->
    <flux:card class="flex flex-col gap-3">
        <flux:heading size="md">{{ __('Bill / Receipt Document') }}</flux:heading>

        @if ($this->payment->bill_path)
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-xl">📄</span>
                        <div>
                            <div class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ basename($this->payment->bill_path) }}</div>
                            <div class="text-xs text-zinc-500">{{ __('Attached Receipt') }}</div>
                        </div>
                    </div>
                    <a href="{{ Storage::url($this->payment->bill_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 border border-indigo-200 dark:border-indigo-800">
                        <span>{{ __('Open in New Tab') }}</span> ↗
                    </a>
                </div>

                @php
                    $extension = strtolower(pathinfo($this->payment->bill_path, PATHINFO_EXTENSION));
                @endphp
                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
                    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 flex justify-center p-2">
                        <img src="{{ Storage::url($this->payment->bill_path) }}" alt="Receipt Attachment" class="max-h-[500px] w-auto object-contain rounded-lg shadow-sm">
                    </div>
                @endif
            </div>
        @else
            <div class="p-6 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl">
                <flux:text class="text-zinc-400 text-sm">{{ __('No receipt or bill document was attached to this transaction.') }}</flux:text>
            </div>
        @endif
    </flux:card>
</div>
