<?php

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionLogItem;
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

    public function mount(): void
    {
        $this->authorize('viewAny', ProductionLog::class);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function productionLogs()
    {
        $user = Auth::user();

        return ProductionLog::query()
            ->with(['employee', 'items.finishedProduct', 'items.rawMaterial', 'finishedProduct', 'rawMaterial'])
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', fn ($e) => $e->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('items.finishedProduct', fn ($fp) => $fp->where('name', 'like', "%{$this->search}%"))
                  ->orWhereHas('finishedProduct', fn ($fp) => $fp->where('name', 'like', "%{$this->search}%"));
            })
            ->latest('date')
            ->latest('id')
            ->paginate(15);
    }

    #[Computed]
    public function totalFinishedThisMonth(): float
    {
        $user = Auth::user();
        return (float) ProductionLogItem::query()
            ->whereHas('productionLog', function ($q) use ($user) {
                $q->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
                  ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]);
            })
            ->sum('quantity_produced');
    }

    #[Computed]
    public function totalWagesPaidThisMonth(): float
    {
        $user = Auth::user();
        return (float) ProductionLog::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('worker_wage');
    }

    public function deleteLog(int $id): void
    {
        $log = ProductionLog::findOrFail($id);
        $this->authorize('delete', $log);

        $log->delete();
        unset($this->productionLogs);
        unset($this->totalFinishedThisMonth);
        unset($this->totalWagesPaidThisMonth);
        Flux::toast(variant: 'success', text: __('Production log deleted. Stock levels & worker wages adjusted.'));
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Production & Manufacturing') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Log daily worker output, consume raw materials (Baans, Fatta), increase finished product stock, and record worker daily wages.') }}</flux:text>
        </div>

        @can('create', App\Models\ProductionLog::class)
        <flux:button variant="primary" icon="plus" href="{{ route('production.create') }}" wire:navigate>{{ __('Log Production Entry') }}</flux:button>
        @endcan
    </div>

    <!-- Stats -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Units Produced (This Month)') }}</flux:text>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">{{ number_format($this->totalFinishedThisMonth, 0) }} pcs</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Production Wages Logged (This Month)') }}</flux:text>
            <flux:heading size="lg" class="text-green-600 dark:text-green-400">₹{{ number_format($this->totalWagesPaidThisMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Total Production Logs') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->productionLogs->total(), 0) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search worker or finished product...')" icon="magnifying-glass" />
    </div>

    <!-- Table -->
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
                    <flux:table.cell colspan="8" class="text-center text-zinc-500 py-6">{{ __('No production entries logged yet. Click "Log Production Entry" to add one.') }}</flux:cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>



