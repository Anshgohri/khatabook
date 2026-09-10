<?php

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionLog;
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

    // Modal state
    public bool $showModal = false;
    public ?int $editingLogId = null;

    public ?int $employee_id = null;
    public ?int $finished_product_id = null;
    public float $finished_quantity = 1.0;
    public ?int $raw_material_id = null;
    public float $raw_quantity_consumed = 0.0;
    public float $worker_wage = 0.0;
    public string $date = '';
    public string $notes = '';

    public function mount(): void
    {
        $this->authorize('viewAny', ProductionLog::class);
        $this->date = now()->toDateString();
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function employees()
    {
        $user = Auth::user();
        return Employee::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->where('status', 'active')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function finishedProducts()
    {
        $user = Auth::user();
        return Product::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'finished_good')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function rawMaterials()
    {
        $user = Auth::user();
        return Product::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->where('type', 'raw_material')
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function productionLogs()
    {
        $user = Auth::user();

        return ProductionLog::query()
            ->with(['employee', 'finishedProduct', 'rawMaterial'])
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, function ($q) {
                $q->whereHas('employee', fn ($e) => $e->where('name', 'like', "%{$this->search}%"))
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
        return (float) ProductionLog::query()
            ->when(! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->sum('finished_quantity');
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

    public function createLog(): void
    {
        $this->authorize('create', ProductionLog::class);
        $this->reset(['editingLogId', 'employee_id', 'finished_product_id', 'finished_quantity', 'raw_material_id', 'raw_quantity_consumed', 'worker_wage', 'notes']);
        $this->date = now()->toDateString();
        $this->finished_quantity = 1.0;
        $this->showModal = true;
    }

    public function saveLog(): void
    {
        $validated = $this->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'finished_product_id' => ['required', 'exists:products,id'],
            'finished_quantity' => ['required', 'numeric', 'min:0.01'],
            'raw_material_id' => ['nullable', 'exists:products,id'],
            'raw_quantity_consumed' => ['nullable', 'numeric', 'min:0'],
            'worker_wage' => ['nullable', 'numeric', 'min:0'],
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $this->authorize('create', ProductionLog::class);

        ProductionLog::create([
            ...$validated,
            'user_id' => Auth::id(),
            'raw_quantity_consumed' => $validated['raw_quantity_consumed'] ?? 0.0,
            'worker_wage' => $validated['worker_wage'] ?? 0.0,
        ]);

        $this->showModal = false;
        unset($this->productionLogs);
        unset($this->totalFinishedThisMonth);
        unset($this->totalWagesPaidThisMonth);
        Flux::toast(variant: 'success', text: __('Production log created. Stock levels & employee wages updated automatically!'));
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
            <flux:text class="mt-1">{{ __('Log daily worker output, consume raw materials (Bans, Fatta), increase finished product stock, and record worker daily wages.') }}</flux:text>
        </div>

        @can('create', App\Models\ProductionLog::class)
        <flux:button variant="primary" icon="plus" wire:click="createLog">{{ __('Log Production Entry') }}</flux:button>
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
                        <flux:badge color="indigo" size="sm">{{ $log->finishedProduct->name ?? '-' }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="font-bold text-green-600 dark:text-green-400">
                        +{{ $log->finished_quantity }} {{ $log->finishedProduct->unit ?? 'pcs' }}
                    </flux:table.cell>
                    <flux:table.cell>
                        @if ($log->rawMaterial)
                            <span class="text-red-600 dark:text-red-400 font-semibold">-{{ $log->raw_quantity_consumed }} {{ $log->rawMaterial->unit ?? 'pcs' }}</span>
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
                        @can('delete', $log)
                        <flux:button size="sm" variant="ghost" icon="trash" wire:click="deleteLog({{ $log->id }})" wire:confirm="{{ __('Delete this production entry? This will revert stock levels & worker payment!') }}" />
                        @endcan
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

    <!-- Modal -->
    <flux:modal wire:model.self="showModal" class="md:w-[500px]">
        <div class="flex flex-col gap-6">
            <flux:heading size="lg">{{ __('Log Daily Production & Wages') }}</flux:heading>

            <form wire:submit="saveLog" class="flex flex-col gap-4">
                <flux:select wire:model="employee_id" :label="__('Worker / Employee')" required>
                    <flux:select.option value="">{{ __('Select Worker (e.g. Talib)') }}</flux:select.option>
                    @foreach ($this->employees as $emp)
                        <flux:select.option :value="$emp->id">{{ $emp->name }} ({{ ucfirst($emp->wage_type) }})</flux:select.option>
                    @endforeach
                </flux:select>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select wire:model="finished_product_id" :label="__('Finished Product Made')" required>
                        <flux:select.option value="">{{ __('Select Product') }}</flux:select.option>
                        @foreach ($this->finishedProducts as $fp)
                            <flux:select.option :value="$fp->id">{{ $fp->name }} (Stock: {{ $fp->stock_level }})</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="number" step="0.01" min="0.01" wire:model="finished_quantity" :label="__('Quantity Produced')" required />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:select wire:model="raw_material_id" :label="__('Raw Material Consumed (Optional)')">
                        <flux:select.option value="">{{ __('None / N/A') }}</flux:select.option>
                        @foreach ($this->rawMaterials as $rm)
                            <flux:select.option :value="$rm->id">{{ $rm->name }} (Stock: {{ $rm->stock_level }})</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="number" step="0.01" min="0" wire:model="raw_quantity_consumed" :label="__('Raw Qty Used')" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <flux:input type="number" step="0.01" min="0" wire:model="worker_wage" :label="__('Worker Daily Wage (₹)')" placeholder="e.g. 500" />
                    <flux:input type="date" wire:model="date" :label="__('Production Date')" required />
                </div>

                <flux:textarea wire:model="notes" :label="__('Remarks / Notes')" placeholder="e.g. Made 50 ladder frames using Assam bans" rows="2" />

                <div class="flex justify-end gap-2">
                    <flux:button type="button" variant="ghost" wire:click="$set('showModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Production Log') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
