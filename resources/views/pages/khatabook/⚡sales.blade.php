<?php

use App\Models\Sale;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Sales')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $paymentStatus = '';

    #[Url]
    public string $dateFrom = '';

    #[Url]
    public string $dateTo = '';

    public function mount(): void
    {
        $this->authorize('viewAny', Sale::class);
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'paymentStatus', 'dateFrom', 'dateTo'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function sales()
    {
        $user = Auth::user();

        return Sale::query()
            ->with(['user', 'customer', 'items.product'])
            ->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id))
            ->when($this->search, fn($query) => $query->where('customer_name', 'like', "%{$this->search}%")->orWhere('items_sold', 'like', "%{$this->search}%"))
            ->when($this->paymentStatus, fn($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->paginate(15);
    }

    public function deleteSale(int $saleId): void
    {
        $sale = Sale::findOrFail($saleId);

        $this->authorize('delete', $sale);

        $sale->delete();
        unset($this->sales);
        Flux::toast(variant: 'success', text: __('Sale deleted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Sales') }}</flux:heading>

        @can('create', Sale::class)
        <flux:button variant="primary" icon="plus" :href="route('sales.create')" wire:navigate>{{ __('Add sale') }}</flux:button>
        @endcan
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search customer or product...')" icon="magnifying-glass" />

        <flux:select wire:model.live="paymentStatus" :placeholder="__('All payment statuses')">
            <flux:select.option value="">{{ __('All payment statuses') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
            <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
        </flux:select>

        <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
        <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->sales">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Items Sold') }}</flux:table.column>
                <flux:table.column>{{ __('Total Qty') }}</flux:table.column>
                <flux:table.column>{{ __('Total Amount') }}</flux:table.column>
                <flux:table.column>{{ __('Status') }}</flux:table.column>
                <flux:table.column>{{ __('Recorded by') }}</flux:table.column>
                <flux:table.column></flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->sales as $sale)
                <flux:table.row wire:key="sale-{{ $sale->id }}">
                    <flux:table.cell>{{ $sale->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $sale->customer_name }}</span>
                            @if ($sale->customer?->phone || $sale->customer?->city)
                            <span class="text-xs text-zinc-500">
                                {{ implode(' • ', array_filter([$sale->customer?->phone, $sale->customer?->city])) }}
                            </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <div class="max-w-xs truncate" title="{{ $sale->items_sold }}">
                            {{ $sale->items_sold }}
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $sale->quantity }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex flex-col">
                            <span class="font-semibold text-emerald-600 dark:text-emerald-400">
                                ₹{{ number_format((float) $sale->total_amount, 2) }}
                            </span>
                            @if ((float) $sale->discount > 0)
                            <span class="text-xs text-amber-600 dark:text-amber-400 font-medium">
                                (Disc: ₹{{ number_format((float) $sale->discount, 2) }})
                            </span>
                            @endif
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="match ($sale->payment_status) { 'paid' => 'green', 'partial' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($sale->payment_status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $sale->user->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex gap-2">
                            @can('update', $sale)
                            <flux:button size="sm" variant="subtle" icon="pencil" :href="route('sales.edit', $sale->id)" wire:navigate>{{ __('Edit') }}</flux:button>
                            @endcan
                            @can('delete', $sale)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteSale({{ $sale->id }})" wire:confirm="{{ __('Delete this sale?') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500">{{ __('No sales found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>