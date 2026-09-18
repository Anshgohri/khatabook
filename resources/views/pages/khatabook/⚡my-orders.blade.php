<?php

use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('My Orders & Purchases')] class extends Component {
    use WithPagination;

    public string $search = '';

    public string $paymentFilter = '';

    public function mount(): void
    {
        $user = Auth::user();
        if (! $user->isCustomer() && ! $user->isAdmin()) {
            // Keep accessible for Customer or Admin previewing
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingPaymentFilter(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function sales()
    {
        $userId = Auth::id();

        return Sale::query()
            ->where('customer_id', $userId)
            ->with(['items.product'])
            ->when($this->search !== '', function ($query) {
                $term = '%'.trim($this->search).'%';
                $query->where(function ($q) use ($term) {
                    $q->where('id', 'like', $term)
                        ->orWhere('items_sold', 'like', $term)
                        ->orWhere('notes', 'like', $term);
                });
            })
            ->when($this->paymentFilter !== '', fn ($q) => $q->where('payment_status', $this->paymentFilter))
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(12);
    }

    #[Computed]
    public function totalOrdersCount(): int
    {
        return Sale::query()->where('customer_id', Auth::id())->count();
    }

    #[Computed]
    public function totalSpentAmount(): float
    {
        return (float) Sale::query()->where('customer_id', Auth::id())->sum('total_amount');
    }

    #[Computed]
    public function pendingPaymentCount(): int
    {
        return Sale::query()
            ->where('customer_id', Auth::id())
            ->whereIn('payment_status', ['unpaid', 'partial'])
            ->count();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <flux:heading size="xl">{{ __('My Orders & Purchases') }}</flux:heading>
            <flux:subheading>{{ __('View your complete purchase history, payment status, and download tax invoices.') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" icon="globe-alt" href="{{ route('home') }}">
                {{ __('Explore Store') }}
            </flux:button>
            <flux:button variant="primary" icon="archive-box" href="{{ route('products') }}" wire:navigate>
                {{ __('View Products') }}
            </flux:button>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="grid gap-4 sm:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Orders Placed') }}</flux:text>
            <flux:heading size="lg">{{ $this->totalOrdersCount }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Amount Spent') }}</flux:text>
            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->totalSpentAmount, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Pending / Unpaid Invoices') }}</flux:text>
            <flux:heading size="lg" class="{{ $this->pendingPaymentCount > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-700 dark:text-zinc-300' }}">
                {{ $this->pendingPaymentCount }}
            </flux:heading>
        </flux:card>
    </div>

    <!-- Filters -->
    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-between gap-4">
        <flux:input wire:model.live.debounce.300ms="search" icon="magnifying-glass" :placeholder="__('Search order ID or items...')" class="max-w-xs" />

        <flux:select wire:model.live="paymentFilter" :placeholder="__('All Payment Statuses')" class="max-w-xs">
            <flux:select.option value="">{{ __('All Payment Statuses') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
            <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
        </flux:select>
    </div>

    <!-- Purchases Table -->
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->sales">
            <flux:table.columns>
                <flux:table.column>{{ __('Invoice #') }}</flux:table.column>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Items Purchased') }}</flux:table.column>
                <flux:table.column>{{ __('Payment Status') }}</flux:table.column>
                <flux:table.column>{{ __('Net Total') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->sales as $sale)
                <flux:table.row wire:key="my-order-{{ $sale->id }}">
                    <flux:table.cell class="font-mono font-bold text-zinc-900 dark:text-zinc-100">
                        #INV-{{ str_pad((string) $sale->id, 5, '0', STR_PAD_LEFT) }}
                    </flux:table.cell>

                    <flux:table.cell class="text-xs text-zinc-600 dark:text-zinc-400">
                        {{ $sale->date?->format('M d, Y') ?? '-' }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex flex-col gap-1 max-w-sm">
                            @if ($sale->items->isNotEmpty())
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($sale->items as $item)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs bg-zinc-100 dark:bg-zinc-800 text-zinc-800 dark:text-zinc-200 border border-zinc-200 dark:border-zinc-700">
                                            {{ $item->product?->name ?? 'Product' }} &times; {{ $item->quantity }}
                                        </span>
                                    @endforeach
                                </div>
                            @else
                                <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">{{ $sale->items_sold }}</span>
                            @endif
                        </div>
                    </flux:table.cell>

                    <flux:table.cell>
                        <flux:badge :color="match ($sale->payment_status) { 'paid' => 'green', 'partial' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($sale->payment_status) }}
                        </flux:badge>
                    </flux:table.cell>

                    <flux:table.cell class="font-extrabold text-emerald-600 dark:text-emerald-400 text-base">
                        ₹{{ number_format((float) $sale->total_amount, 2) }}
                    </flux:table.cell>

                    <flux:table.cell>
                        <div class="flex items-center gap-1.5 justify-end">
                            <flux:button size="xs" variant="ghost" icon="eye" href="{{ route('invoices.sale.view', $sale) }}" target="_blank" title="{{ __('View PDF Invoice') }}" />
                            <flux:button size="xs" variant="ghost" icon="arrow-down-tray" href="{{ route('invoices.sale.download', $sale) }}" title="{{ __('Download PDF Invoice') }}" />
                            <flux:button size="xs" variant="ghost" icon="printer" href="{{ route('invoices.sale.print', $sale) }}" target="_blank" title="{{ __('Print Invoice') }}" />
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="6" class="text-center text-zinc-500 py-12">
                        <div class="flex flex-col items-center gap-2">
                            <flux:icon name="shopping-bag" class="w-8 h-8 text-zinc-400" />
                            <span class="font-semibold text-zinc-700 dark:text-zinc-300">{{ __('No purchases found.') }}</span>
                            <span class="text-xs text-zinc-500">{{ __('When you make purchases at the store, your tax invoices and order history will appear here.') }}</span>
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
