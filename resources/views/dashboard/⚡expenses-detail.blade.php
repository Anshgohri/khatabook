<?php

use App\Models\Expense;
use Flux\Flux;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Expense Details')] class extends Component {
    public Expense $expense;

    public function mount(Expense $expense): void
    {
        $this->authorize('view', $expense);
        $this->expense = $expense->load(['user', 'category']);
    }
}; ?>

<div class="max-w-3xl mx-auto flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" :href="route('expenses')" wire:navigate>
                {{ __('Back to Expenses') }}
            </flux:button>
            <div>
                <flux:heading size="xl">{{ __('Expense Details') }}</flux:heading>
                <flux:subheading size="sm">
                    {{ __('ID: #:id', ['id' => $this->expense->id]) }} • {{ $this->expense->date->format('d F Y') }}
                </flux:subheading>
            </div>
        </div>

        <div class="flex items-center gap-2">
            @can('update', $this->expense)
            <flux:button variant="primary" icon="pencil" :href="route('expenses.edit', $this->expense->id)" wire:navigate>
                {{ __('Edit Entry') }}
            </flux:button>
            @endcan
        </div>
    </div>

    <!-- Main Expense Voucher Card -->
    <flux:card class="flex flex-col gap-6">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-6 border-b border-zinc-200 dark:border-zinc-700">
            <div class="flex flex-col gap-2">
                @php
                    $cColor = $this->expense->category->color ?? 'emerald';
                    $cIcon = $this->expense->category->icon ?? '📁';
                    $cType = $this->expense->category->type ?? 'business';
                @endphp
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg text-xs font-bold bg-slate-900 dark:bg-slate-950 border border-slate-700 shadow-sm text-white">
                        <span>{{ $cIcon }}</span>
                        <span>{{ $this->expense->category->name }}</span>
                    </span>
                    <span class="text-[10px] font-black uppercase tracking-wider px-2 py-0.5 rounded bg-slate-200 dark:bg-slate-800 text-slate-700 dark:text-slate-300">
                        {{ $cType }}
                    </span>
                </div>

                <div class="flex items-baseline gap-2 mt-1">
                    <span class="text-4xl font-extrabold tracking-tight text-rose-600 dark:text-rose-400">
                        ₹{{ number_format((float) $this->expense->amount, 2) }}
                    </span>
                </div>

                <flux:text size="sm" class="text-zinc-500">
                    {{ __('Paid via :method', ['method' => strtoupper(str_replace('_', ' ', $this->expense->payment_method))]) }}
                </flux:text>
            </div>

            <div class="sm:text-right flex flex-col gap-1">
                <flux:text size="xs">{{ __('Expense Date') }}</flux:text>
                <span class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">{{ $this->expense->date->format('d M Y') }}</span>
                <span class="text-xs text-zinc-400">{{ $this->expense->date->diffForHumans() }}</span>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 gap-6">
            <div>
                <flux:text size="xs">{{ __('Payment Method') }}</flux:text>
                <div class="mt-1 font-semibold uppercase text-zinc-800 dark:text-zinc-200 text-sm">
                    {{ str_replace('_', ' ', $this->expense->payment_method) }}
                </div>
            </div>

            <div>
                <flux:text size="xs">{{ __('Recorded By') }}</flux:text>
                <div class="mt-1 font-semibold text-zinc-800 dark:text-zinc-200 text-sm">
                    {{ $this->expense->user?->name ?? '—' }}
                </div>
            </div>

            <div>
                <flux:text size="xs">{{ __('Recorded On') }}</flux:text>
                <div class="mt-1 font-medium text-zinc-600 dark:text-zinc-400 text-sm">
                    {{ $this->expense->created_at->format('d M Y, h:i A') }}
                </div>
            </div>
        </div>
    </flux:card>

    <!-- Description Card -->
    <flux:card class="flex flex-col gap-2">
        <flux:heading size="md">{{ __('Description / Item Details') }}</flux:heading>
        @if ($this->expense->description)
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 text-sm whitespace-pre-wrap leading-relaxed">
                {{ $this->expense->description }}
            </div>
        @else
            <flux:text class="text-zinc-400 italic text-sm">{{ __('No description recorded for this expense.') }}</flux:text>
        @endif
    </flux:card>

    <!-- Notes Card -->
    @if ($this->expense->notes)
        <flux:card class="flex flex-col gap-2">
            <flux:heading size="md">{{ __('Internal Notes') }}</flux:heading>
            <div class="p-4 rounded-xl bg-zinc-50 dark:bg-zinc-800/60 border border-zinc-200 dark:border-zinc-700 text-zinc-800 dark:text-zinc-200 text-sm whitespace-pre-wrap leading-relaxed">
                {{ $this->expense->notes }}
            </div>
        </flux:card>
    @endif

    <!-- Receipt / Bill Document Card -->
    <flux:card class="flex flex-col gap-3">
        <flux:heading size="md">{{ __('Bill / Receipt Document') }}</flux:heading>

        @if ($this->expense->bill_path)
            <div class="flex flex-col gap-3">
                <div class="flex items-center justify-between p-3 rounded-xl bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700">
                    <div class="flex items-center gap-2 truncate">
                        <span class="text-xl">📄</span>
                        <div>
                            <div class="font-semibold text-sm text-zinc-900 dark:text-zinc-100 truncate">{{ basename($this->expense->bill_path) }}</div>
                            <div class="text-xs text-zinc-500">{{ __('Attached Receipt') }}</div>
                        </div>
                    </div>
                    <a href="{{ Storage::url($this->expense->bill_path) }}" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 hover:bg-indigo-100 border border-indigo-200 dark:border-indigo-800">
                        <span>{{ __('Open in New Tab') }}</span> ↗
                    </a>
                </div>

                @php
                    $extension = strtolower(pathinfo($this->expense->bill_path, PATHINFO_EXTENSION));
                @endphp
                @if (in_array($extension, ['jpg', 'jpeg', 'png', 'webp', 'gif'], true))
                    <div class="rounded-xl overflow-hidden border border-zinc-200 dark:border-zinc-700 bg-zinc-100 dark:bg-zinc-900 flex justify-center p-2">
                        <img src="{{ Storage::url($this->expense->bill_path) }}" alt="Receipt Attachment" class="max-h-[500px] w-auto object-contain rounded-lg shadow-sm">
                    </div>
                @endif
            </div>
        @else
            <div class="p-6 text-center border-2 border-dashed border-zinc-200 dark:border-zinc-700 rounded-xl">
                <flux:text class="text-zinc-400 text-sm">{{ __('No receipt or bill document was attached to this expense.') }}</flux:text>
            </div>
        @endif
    </flux:card>
</div>
