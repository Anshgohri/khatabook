<?php

use App\Models\Sale;
use App\Services\ExcelSyncService;
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
    public string $datePreset = '';

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

    public function updatedDatePreset(): void
    {
        switch ($this->datePreset) {
            case 'today':
                $this->dateFrom = now()->toDateString();
                $this->dateTo = now()->toDateString();
                break;
            case 'yesterday':
                $this->dateFrom = now()->subDay()->toDateString();
                $this->dateTo = now()->subDay()->toDateString();
                break;
            case 'week':
                $this->dateFrom = now()->startOfWeek()->toDateString();
                $this->dateTo = now()->endOfWeek()->toDateString();
                break;
            case 'month':
                $this->dateFrom = now()->startOfMonth()->toDateString();
                $this->dateTo = now()->endOfMonth()->toDateString();
                break;
            case 'custom':
                // Do not change dates for custom
                break;
            default:
                $this->dateFrom = '';
                $this->dateTo = '';
                break;
        }
        $this->resetPage();
    }

    #[Computed]
    public function sales()
    {
        $user = Auth::user();

        return Sale::query()
            ->with(['user', 'customer', 'items.product'])
            ->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id))
            ->when($this->search, function ($query) {
                $query->where('customer_name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhere('items_sold', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%");
                      });
            })
            ->when($this->paymentStatus, fn($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->latest('date')
            ->paginate(15);
    }

    #[Computed]
    public function dailyRevenue(): float
    {
        return (float) Sale::query()
            ->whereDate('date', today())
            ->sum('total_amount');
    }

    #[Computed]
    public function dailyProfit(): float
    {
        $user = Auth::user();
        if (! $user?->isAdmin()) {
            return 0.0;
        }

        return (float) Sale::query()
            ->whereDate('date', today())
            ->with('items.product')
            ->get()
            ->sum(fn ($sale) => $sale->profit());
    }

    #[Computed]
    public function dailyProductionCost(): float
    {
        $user = Auth::user();
        if (! $user?->isAdmin()) {
            return 0.0;
        }

        return (float) Sale::query()
            ->whereDate('date', today())
            ->with('items.product')
            ->get()
            ->sum(fn ($sale) => $sale->totalCost());
    }

    #[Computed]
    public function filteredProfit(): float
    {
        $user = Auth::user();
        if (! $user?->isAdmin()) {
            return 0.0;
        }

        return (float) Sale::query()
            ->with('items.product')
            ->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id))
            ->when($this->search, function ($query) {
                $query->where('customer_name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhere('items_sold', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%");
                      });
            })
            ->when($this->paymentStatus, fn($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->get()
            ->sum(fn ($sale) => $sale->profit());
    }

    #[Computed]
    public function filteredProductionCost(): float
    {
        $user = Auth::user();
        if (! $user?->isAdmin()) {
            return 0.0;
        }

        return (float) Sale::query()
            ->with('items.product')
            ->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id))
            ->when($this->search, function ($query) {
                $query->where('customer_name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhere('items_sold', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                      ->orWhereHas('customer', function ($q) {
                          $q->where('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%");
                      });
            })
            ->when($this->paymentStatus, fn($query) => $query->where('payment_status', $this->paymentStatus))
            ->when($this->dateFrom, fn($query) => $query->whereDate('date', '>=', $this->dateFrom))
            ->when($this->dateTo, fn($query) => $query->whereDate('date', '<=', $this->dateTo))
            ->get()
            ->sum(fn ($sale) => $sale->totalCost());
    }

    public function deleteSale(int $saleId): void
    {
        $sale = Sale::findOrFail($saleId);

        $this->authorize('delete', $sale);

        $sale->delete();
        unset($this->sales);
        Flux::toast(variant: 'success', text: __('Sale deleted.'));
    }

    #[Computed]
    public function lastExcelSync(): ?string
    {
        return app(ExcelSyncService::class)->lastSyncedAt();
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Sales') }}</flux:heading>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Excel Export / Backup Button --}}
            <a
                href="{{ route('sales.export.excel') }}"
                class="inline-flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg border border-emerald-300 dark:border-emerald-700 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/60 transition-colors"
                title="{{ __('Download synced Excel backup of all sales') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                {{ __('Export Excel') }}
            </a>

            @if ($this->lastExcelSync)
                <span class="text-xs text-zinc-400 dark:text-zinc-500 hidden sm:block">
                    {{ __('Last synced:') }} {{ $this->lastExcelSync }}
                </span>
            @endif

            @can('create', Sale::class)
            <flux:button variant="primary" icon="plus" :href="route('sales.create')" wire:navigate>{{ __('Add sale') }}</flux:button>
            @endcan
        </div>
    </div>

    @if (auth()->user()?->isAdmin())
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10">
            <flux:text size="sm" class="font-medium text-emerald-800 dark:text-emerald-300">{{ __('Today\'s Profit (Earned)') }}</flux:text>
            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->dailyProfit, 2) }}</flux:heading>
            <span class="text-xs text-zinc-500">{{ __('Sales Today:') }} ₹{{ number_format($this->dailyRevenue, 2) }}</span>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-indigo-500 bg-indigo-50/20 dark:bg-indigo-950/10">
            <flux:text size="sm" class="font-medium text-indigo-800 dark:text-indigo-300">{{ __('Profit (Filtered Period)') }}</flux:text>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">₹{{ number_format($this->filteredProfit, 2) }}</flux:heading>
            <span class="text-xs text-zinc-500">{{ __('Calculated from item unit costs') }}</span>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-amber-500 bg-amber-50/20 dark:bg-amber-950/10">
            <flux:text size="sm" class="font-medium text-amber-800 dark:text-amber-300">{{ __('Today\'s Production Cost') }}</flux:text>
            <flux:heading size="lg" class="text-amber-600 dark:text-amber-400">₹{{ number_format($this->dailyProductionCost, 2) }}</flux:heading>
            <span class="text-xs text-zinc-500">{{ __('Cost of items sold today') }}</span>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500 bg-orange-50/20 dark:bg-orange-950/10">
            <flux:text size="sm" class="font-medium text-orange-800 dark:text-orange-300">{{ __('Produced Cost (Filtered)') }}</flux:text>
            <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->filteredProductionCost, 2) }}</flux:heading>
            <span class="text-xs text-zinc-500">{{ __('Calculated from item unit costs') }}</span>
        </flux:card>
    </div>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-5 items-end">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search customer or product...')" icon="magnifying-glass" />

        <flux:select wire:model.live="paymentStatus" :placeholder="__('All payment statuses')">
            <flux:select.option value="">{{ __('All payment statuses') }}</flux:select.option>
            <flux:select.option value="paid">{{ __('Paid') }}</flux:select.option>
            <flux:select.option value="partial">{{ __('Partial') }}</flux:select.option>
            <flux:select.option value="unpaid">{{ __('Unpaid') }}</flux:select.option>
        </flux:select>
        
        <flux:select wire:model.live="datePreset" :label="__('Date Range')">
            <flux:select.option value="">{{ __('All Time') }}</flux:select.option>
            <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
            <flux:select.option value="yesterday">{{ __('Yesterday') }}</flux:select.option>
            <flux:select.option value="week">{{ __('This Week') }}</flux:select.option>
            <flux:select.option value="month">{{ __('This Month') }}</flux:select.option>
            <flux:select.option value="custom">{{ __('Custom Range') }}</flux:select.option>
        </flux:select>

        @if ($datePreset === 'custom')
        <flux:input type="date" wire:model.live="dateFrom" :label="__('From')" />
        <flux:input type="date" wire:model.live="dateTo" :label="__('To')" />
        @endif
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->sales">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Customer') }}</flux:table.column>
                <flux:table.column>{{ __('Items Sold') }}</flux:table.column>
                <flux:table.column>{{ __('Total Qty') }}</flux:table.column>
                <flux:table.column>{{ __('Total Amount') }}</flux:table.column>
                @if (auth()->user()?->isAdmin())
                <flux:table.column>{{ __('Profit') }}</flux:table.column>
                <flux:table.column>{{ __('Produced Cost') }}</flux:table.column>
                @endif
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
                    @if (auth()->user()?->isAdmin())
                    <flux:table.cell>
                        <span class="font-semibold {{ $sale->profit() >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">
                            ₹{{ number_format((float) $sale->profit(), 2) }}
                        </span>
                    </flux:table.cell>
                    <flux:table.cell>
                        <span class="font-semibold text-amber-600 dark:text-amber-400">
                            ₹{{ number_format((float) $sale->totalCost(), 2) }}
                        </span>
                    </flux:table.cell>
                    @endif
                    <flux:table.cell>
                        <flux:badge :color="match ($sale->payment_status) { 'paid' => 'green', 'partial' => 'amber', default => 'red' }" size="sm">
                            {{ ucfirst($sale->payment_status) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $sale->user->name }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-2">
                            <flux:dropdown position="bottom" align="end">
                                <flux:button size="sm" variant="subtle" icon="document-text">
                                    {{ __('Invoice') }}
                                </flux:button>
                                <flux:menu>
                                    <flux:menu.item icon="printer" :href="route('invoices.sale.print', $sale->id)" target="_blank">
                                        {{ __('Print / Preview Invoice') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="arrow-down-tray" :href="route('invoices.sale.download', $sale->id)">
                                        {{ __('Download PDF') }}
                                    </flux:menu.item>
                                    <flux:menu.item icon="eye" :href="route('invoices.sale.view', $sale->id)" target="_blank">
                                        {{ __('View Stream PDF') }}
                                    </flux:menu.item>
                                </flux:menu>
                            </flux:dropdown>

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
                    <flux:table.cell colspan="{{ auth()->user()?->isAdmin() ? 10 : 8 }}" class="text-center text-zinc-500">{{ __('No sales found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>