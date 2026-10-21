<?php

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionLogItem;
use App\Models\SaleItem;
use Carbon\Carbon;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Production Log')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $preset = 'this_month';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    public function mount(): void
    {
        $this->authorize('viewAny', ProductionLog::class);

        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth()->toDateString();
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->toDateString();
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatedPreset(string $val): void
    {
        switch ($val) {
            case 'today':
                $this->startDate = now()->toDateString();
                $this->endDate   = now()->toDateString();
                break;
            case 'yesterday':
                $this->startDate = now()->subDay()->toDateString();
                $this->endDate   = now()->subDay()->toDateString();
                break;
            case 'this_week':
                $this->startDate = now()->startOfWeek()->toDateString();
                $this->endDate   = now()->toDateString();
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->toDateString();
                $this->endDate   = now()->toDateString();
                break;
            case 'last_month':
                $this->startDate = now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate   = now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $this->startDate = now()->startOfYear()->toDateString();
                $this->endDate   = now()->toDateString();
                break;
            case 'all_time':
                $this->startDate = '2000-01-01';
                $this->endDate   = now()->addYear()->toDateString();
                break;
        }
    }

    #[Computed]
    public function dateRange(): array
    {
        if ($this->preset === 'custom' && ! empty($this->startDate) && ! empty($this->endDate)) {
            return [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ];
        }

        return match ($this->preset) {
            'today'      => [now()->startOfDay(), now()->endOfDay()],
            'yesterday'  => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_week'  => [now()->startOfWeek(), now()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year'  => [now()->startOfYear(), now()->endOfYear()],
            'all_time'   => [Carbon::parse('2000-01-01'), now()->addYear()],
            default      => [
                ! empty($this->startDate) ? Carbon::parse($this->startDate)->startOfDay() : now()->startOfMonth(),
                ! empty($this->endDate)   ? Carbon::parse($this->endDate)->endOfDay()     : now()->endOfDay(),
            ],
        };
    }

    #[Computed]
    public function productionLogs()
    {
        $user = Auth::user();

        return ProductionLog::query()
            ->with(['employee', 'items.finishedProduct', 'items.rawMaterial', 'finishedProduct', 'rawMaterial'])
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', fn ($e) => $e->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%"))
                  ->orWhereHas('items.finishedProduct', fn ($fp) => $fp->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%"))
                  ->orWhereHas('finishedProduct', fn ($fp) => $fp->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%"));
            })
            ->latest('date')
            ->latest('id')
            ->paginate(15);
    }

    #[Computed]
    public function totalFinishedInPeriod(): int
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        return (int) ProductionLogItem::query()
            ->whereHas('productionLog', function ($q) use ($from, $to, $user) {
                $q->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
                  ->whereBetween('date', [$from, $to]);
            })
            ->sum('quantity_produced');
    }

    #[Computed]
    public function totalWagesPaidInPeriod(): float
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        return (float) ProductionLog::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('date', [$from, $to])
            ->sum('worker_wage');
    }

    /**
     * Total Ghodi units produced in the selected period.
     * Matches any ProductionLogItem whose finishedProduct name contains "ghodi".
     */
    #[Computed]
    public function totalGhodiProduced(): int
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        return (int) ProductionLogItem::query()
            ->whereHas('productionLog', function ($q) use ($from, $to, $user) {
                $q->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
                  ->whereBetween('date', [$from, $to]);
            })
            ->whereHas('finishedProduct', fn ($q) => $q->where('name', \App\Providers\AppServiceProvider::likeOperator(), '%ghodi%'))
            ->sum('quantity_produced');
    }

    /**
     * Total Ghodi units sold in the selected period.
     * Matches any SaleItem whose product name contains "ghodi".
     */
    #[Computed]
    public function totalGhodiSold(): int
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        return (int) SaleItem::query()
            ->whereHas('sale', function ($q) use ($from, $to, $user) {
                $q->whereBetween('date', [$from, $to])
                  ->when($user->isStaff(), fn ($sq) => $sq->where('user_id', $user->id));
            })
            ->whereHas('product', fn ($q) => $q->where('name', \App\Providers\AppServiceProvider::likeOperator(), '%ghodi%'))
            ->sum('quantity');
    }

    public function deleteLog(int $id): void
    {
        $log = ProductionLog::findOrFail($id);
        $this->authorize('delete', $log);

        $log->delete();
        unset($this->productionLogs);
        unset($this->totalFinishedInPeriod);
        unset($this->totalWagesPaidInPeriod);
        unset($this->totalGhodiProduced);
        unset($this->totalGhodiSold);
        Flux::toast(variant: 'success', text: __('Production log deleted. Stock levels & worker wages adjusted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Production & Manufacturing') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Log daily worker output, consume raw materials (Baans, Fatta), increase finished product stock, and record worker daily wages.') }}</flux:text>
        </div>

        @can('create', App\Models\ProductionLog::class)
        <flux:button variant="primary" icon="plus" href="{{ route('production.create') }}" wire:navigate>{{ __('Log Production Entry') }}</flux:button>
        @endcan
    </div>

    {{-- Date Filter Panel --}}
    <div class="bg-white dark:bg-zinc-900 p-4 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
            {{-- Preset --}}
            <div>
                <flux:label>{{ __('Time Period') }}</flux:label>
                <flux:select wire:model.live="preset" class="mt-1">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="yesterday">{{ __('Yesterday') }}</flux:select.option>
                    <flux:select.option value="this_week">{{ __('This Week') }}</flux:select.option>
                    <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
                    <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
                    <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
                    <flux:select.option value="all_time">{{ __('All Time') }}</flux:select.option>
                    <flux:select.option value="custom">{{ __('Custom Range') }}</flux:select.option>
                </flux:select>
            </div>

            {{-- From Date --}}
            <div>
                <flux:label>{{ __('From') }}</flux:label>
                <flux:input type="date" wire:model.live="startDate" class="mt-1" :disabled="$preset !== 'custom'" />
            </div>

            {{-- To Date --}}
            <div>
                <flux:label>{{ __('To') }}</flux:label>
                <flux:input type="date" wire:model.live="endDate" class="mt-1" :disabled="$preset !== 'custom'" />
            </div>

            {{-- Search --}}
            <div>
                <flux:label>{{ __('Search') }}</flux:label>
                <flux:input wire:model.live.debounce.400ms="search" placeholder="{{ __('Worker or product...') }}" icon="magnifying-glass" class="mt-1" />
            </div>
        </div>
    </div>

    {{-- Ghodi Produced vs Sold Comparison --}}
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm p-5 flex flex-col gap-4">
        <div>
            <h3 class="font-extrabold text-lg text-zinc-900 dark:text-white flex items-center gap-2">
                🪜 {{ __('Ghodi — Produced vs Sold') }}
                <span class="px-2 py-0.5 rounded-full text-[10px] font-black bg-orange-500/20 text-orange-600 dark:text-orange-400 border border-orange-500/40 uppercase">Comparison</span>
            </h3>
            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                {{ __('Compare how many Ghodi were manufactured vs sold in the selected period.') }}
                &nbsp;·&nbsp;
                <span class="font-semibold">{{ \Carbon\Carbon::parse($this->startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($this->endDate)->format('d M Y') }}</span>
            </p>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            {{-- Produced --}}
            <div class="flex flex-col gap-2 p-4 rounded-xl bg-indigo-50/60 dark:bg-indigo-950/30 border border-indigo-200 dark:border-indigo-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wide">🏭 {{ __('Ghodi Produced') }}</span>
                    <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 text-lg">🔨</span>
                </div>
                <div class="text-3xl font-black text-indigo-900 dark:text-indigo-100">
                    {{ number_format($this->totalGhodiProduced) }}
                    <span class="text-sm font-semibold text-indigo-500 dark:text-indigo-400">pcs</span>
                </div>
                <div class="text-[11px] text-indigo-600 dark:text-indigo-400">From production logs in period</div>
            </div>

            {{-- Sold --}}
            <div class="flex flex-col gap-2 p-4 rounded-xl bg-emerald-50/60 dark:bg-emerald-950/30 border border-emerald-200 dark:border-emerald-800">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wide">🛒 {{ __('Ghodi Sold') }}</span>
                    <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 text-lg">💰</span>
                </div>
                <div class="text-3xl font-black text-emerald-900 dark:text-emerald-100">
                    {{ number_format($this->totalGhodiSold) }}
                    <span class="text-sm font-semibold text-emerald-500 dark:text-emerald-400">pcs</span>
                </div>
                <div class="text-[11px] text-emerald-600 dark:text-emerald-400">From sales records in period</div>
            </div>

            {{-- Difference / Balance --}}
            @php
                $diff = $this->totalGhodiProduced - $this->totalGhodiSold;
                $isPositive = $diff >= 0;
            @endphp
            <div class="flex flex-col gap-2 p-4 rounded-xl {{ $isPositive ? 'bg-amber-50/60 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800' : 'bg-red-50/60 dark:bg-red-950/30 border border-red-200 dark:border-red-800' }}">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-bold {{ $isPositive ? 'text-amber-700 dark:text-amber-300' : 'text-red-700 dark:text-red-300' }} uppercase tracking-wide">
                        {{ $isPositive ? '📦 Remaining Stock' : '⚠️ Stock Deficit' }}
                    </span>
                    <span class="p-1.5 rounded-lg {{ $isPositive ? 'bg-amber-500/10 text-amber-600 dark:text-amber-400' : 'bg-red-500/10 text-red-600 dark:text-red-400' }} text-lg">
                        {{ $isPositive ? '✅' : '🚨' }}
                    </span>
                </div>
                <div class="text-3xl font-black {{ $isPositive ? 'text-amber-900 dark:text-amber-100' : 'text-red-900 dark:text-red-100' }}">
                    {{ $isPositive ? '+' : '' }}{{ number_format($diff) }}
                    <span class="text-sm font-semibold {{ $isPositive ? 'text-amber-500 dark:text-amber-400' : 'text-red-500 dark:text-red-400' }}">pcs</span>
                </div>
                <div class="text-[11px] {{ $isPositive ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $isPositive ? 'Produced more than sold' : 'Sold more than produced (check records)' }}
                </div>
            </div>
        </div>
    </div>

    {{-- General Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('All Units Produced (Period)') }}</flux:text>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">{{ number_format($this->totalFinishedInPeriod, 0) }} pcs</flux:heading>
            <flux:text size="xs" class="text-zinc-400">Across all products</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Worker Wages (Period)') }}</flux:text>
            <flux:heading size="lg" class="text-green-600 dark:text-green-400">₹{{ number_format($this->totalWagesPaidInPeriod, 2) }}</flux:heading>
            <flux:text size="xs" class="text-zinc-400">Total wages logged</flux:text>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Production Logs') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->productionLogs->total(), 0) }}</flux:heading>
            <flux:text size="xs" class="text-zinc-400">All time entries</flux:text>
        </flux:card>
    </div>

    {{-- Table --}}
    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->productionLogs">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Worker / Employee') }}</flux:table.column>
                <flux:table.column>{{ __('Finished Good Created') }}</flux:table.column>
                <flux:table.column>{{ __('Qty Produced') }}</flux:table.column>
                <flux:table.column>{{ __('Raw Material Consumed') }}</flux:table.column>
                <flux:table.column>{{ __('Worker Wage (₹)') }}</flux:table.column>
                <flux:table.column>{{ __('Notes') }}</flux:table.column>
                <flux:table.column>{{ __('Actions') }}</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>
                @forelse ($this->productionLogs as $log)
                <flux:table.row wire:key="log-{{ $log->id }}">
                    <flux:table.cell class="font-medium whitespace-nowrap">{{ $log->date->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell class="font-semibold">{{ $log->employee->name ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($log->items->count() > 0)
                            <div class="flex flex-col gap-1">
                                @foreach ($log->items as $item)
                                    <div class="flex items-center gap-1.5">
                                        <flux:badge color="indigo" size="sm">{{ $item->finishedProduct->name ?? '-' }}</flux:badge>
                                        <span class="text-xs text-zinc-500 font-medium">({{ $item->quantity_produced }} {{ $item->finishedProduct->unit ?? 'pcs' }})</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <flux:badge color="indigo" size="sm">{{ $log->finishedProduct->name ?? '-' }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-bold text-green-600 dark:text-green-400">
                        @if ($log->items->count() > 0)
                            +{{ $log->items->sum('quantity_produced') }} pcs
                        @else
                            +{{ $log->quantity_produced }} {{ $log->finishedProduct->unit ?? 'pcs' }}
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($log->items->count() > 0)
                            <div class="flex flex-col gap-1">
                                @php $hasRaw = false; @endphp
                                @foreach ($log->items as $item)
                                    @if ($item->rawMaterial && $item->raw_material_consumed_qty > 0)
                                        @php $hasRaw = true; @endphp
                                        <div class="text-xs">
                                            <span class="text-red-600 dark:text-red-400 font-semibold">-{{ $item->raw_material_consumed_qty }} {{ $item->rawMaterial->unit ?? 'pcs' }}</span>
                                            <span class="text-zinc-500">({{ $item->rawMaterial->name }})</span>
                                        </div>
                                    @endif
                                @endforeach
                                @if (! $hasRaw)
                                    <span class="text-zinc-400">-</span>
                                @endif
                            </div>
                        @elseif ($log->rawMaterial)
                            <span class="text-red-600 dark:text-red-400 font-semibold">-{{ $log->raw_material_consumed_qty }} {{ $log->rawMaterial->unit ?? 'pcs' }}</span>
                            <span class="text-xs text-zinc-500 block">({{ $log->rawMaterial->name }})</span>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell class="font-bold text-indigo-600 dark:text-indigo-400">
                        ₹{{ number_format((float) $log->worker_wage, 2) }}
                    </flux:table.cell>
                    <flux:table.cell class="text-xs text-zinc-500">{{ $log->notes ?? '-' }}</flux:table.cell>
                    <flux:table.cell>
                        <div class="flex items-center gap-1">
                            @can('update', $log)
                            <flux:button size="sm" variant="ghost" icon="pencil" href="{{ route('production.edit', $log) }}" wire:navigate />
                            @endcan
                            @can('delete', $log)
                            <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteLog({{ $log->id }})" wire:confirm="{{ __('Delete this production entry? This will revert stock levels & worker payment!') }}" />
                            @endcan
                        </div>
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-6">{{ __('No production entries logged yet. Click "Log Production Entry" to add one.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
