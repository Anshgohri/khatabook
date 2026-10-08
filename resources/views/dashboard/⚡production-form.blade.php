<?php

use App\Models\Employee;
use App\Models\Product;
use App\Models\ProductionLog;
use App\Models\ProductionLogItem;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Production Entry Form')] class extends Component {
    public ?int $editingId = null;

    public ?int $employee_id = null;

    public string $date = '';

    public float $worker_wage = 0.0;

    public string $notes = '';

    /**
     * @var array<int, array{finished_product_id: string|int, quantity_produced: int, raw_material_id: string|int, raw_quantity_consumed: int}>
     */
    public array $items = [];

    public function mount(?ProductionLog $productionLog = null): void
    {
        if ($productionLog && $productionLog->exists) {
            $this->authorize('update', $productionLog);
            $productionLog->load(['items.finishedProduct', 'items.rawMaterial']);

            $this->editingId = $productionLog->id;
            $this->employee_id = $productionLog->employee_id;
            $this->date = $productionLog->date ? $productionLog->date->format('Y-m-d') : now()->toDateString();
            $this->worker_wage = (float) $productionLog->worker_wage;
            $this->notes = (string) ($productionLog->notes ?? '');

            $this->items = [];
            if ($productionLog->items->count() > 0) {
                foreach ($productionLog->items as $item) {
                    $this->items[] = [
                        'finished_product_id' => (string) $item->finished_product_id,
                        'quantity_produced' => (int) $item->quantity_produced,
                        'raw_material_id' => $item->raw_material_id ? (string) $item->raw_material_id : '',
                        'raw_quantity_consumed' => (int) $item->raw_material_consumed_qty,
                    ];
                }
            } elseif ($productionLog->finished_product_id) {
                $this->items[] = [
                    'finished_product_id' => (string) $productionLog->finished_product_id,
                    'quantity_produced' => (int) $productionLog->quantity_produced,
                    'raw_material_id' => $productionLog->raw_material_id ? (string) $productionLog->raw_material_id : '',
                    'raw_quantity_consumed' => (int) $productionLog->raw_material_consumed_qty,
                ];
            } else {
                $this->addItem();
            }
        } else {
            $this->authorize('create', ProductionLog::class);
            $this->date = now()->toDateString();
            $this->worker_wage = 0.0;
            $this->items = [
                ['finished_product_id' => '', 'quantity_produced' => 1, 'raw_material_id' => '', 'raw_quantity_consumed' => 0],
            ];
        }
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
        return Product::query()
            ->finished()
            ->orderBy('name')
            ->get();
    }

    #[Computed]
    public function rawMaterials()
    {
        return Product::query()
            ->rawMaterial()
            ->orderBy('name')
            ->get();
    }

    public function addItem(): void
    {
        $this->items[] = [
            'finished_product_id' => '',
            'quantity_produced' => 1,
            'raw_material_id' => '',
            'raw_quantity_consumed' => 0,
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
        }
    }

    #[Computed]
    public function totalQuantityProduced(): int
    {
        return array_reduce($this->items, function ($carry, $item) {
            return $carry + max(0, (int) ($item['quantity_produced'] ?? 0));
        }, 0);
    }

    public function save()
    {
        $validated = $this->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'date' => ['required', 'date'],
            'worker_wage' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.finished_product_id' => ['required', 'exists:products,id'],
            'items.*.quantity_produced' => ['required', 'numeric', 'min:1'],
            'items.*.raw_material_id' => ['nullable', 'exists:products,id'],
            'items.*.raw_quantity_consumed' => ['nullable', 'numeric', 'min:0'],
        ]);

        if ($this->editingId) {
            $log = ProductionLog::findOrFail($this->editingId);
            $this->authorize('update', $log);

            $log->update([
                'employee_id' => $validated['employee_id'],
                'worker_wage' => $validated['worker_wage'] ?? 0.0,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $log->syncItemsAndInventory($validated['items']);

            Flux::toast(variant: 'success', text: __('Production log updated successfully! Stock levels & wages adjusted.'));
        } else {
            $this->authorize('create', ProductionLog::class);

            $log = ProductionLog::create([
                'user_id' => Auth::id(),
                'employee_id' => $validated['employee_id'],
                'worker_wage' => $validated['worker_wage'] ?? 0.0,
                'date' => $validated['date'],
                'notes' => $validated['notes'] ?? null,
            ]);

            $log->syncItemsAndInventory($validated['items']);

            Flux::toast(variant: 'success', text: __('Daily production entry logged successfully! Stock levels & wages updated.'));
        }

        return $this->redirect(route('production'), navigate: true);
    }
}; ?>

<div class="max-w-6xl mx-auto flex flex-col gap-6 pb-12">
    <div class="flex items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <flux:button variant="ghost" icon="arrow-left" href="{{ route('production') }}" wire:navigate>
                {{ __('Back to Production') }}
            </flux:button>
            <flux:heading size="xl">{{ $editingId ? __('Edit Daily Production Log #') . $editingId : __('Log Daily Production') }}</flux:heading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" href="{{ route('production') }}" wire:navigate>{{ __('Cancel') }}</flux:button>
            <flux:button variant="primary" wire:click="save" icon="check">{{ __('Save Production Log') }}</flux:button>
        </div>
    </div>

    <form wire:submit="save" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        <div class="lg:col-span-8 flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Production Header Details') }}</flux:heading>

                <div class="grid gap-4 sm:grid-cols-2">
                    <flux:select wire:model="employee_id" :label="__('Worker / Employee')" required>
                        <flux:select.option value="">{{ __('-- Select Worker (e.g. Talib Khan) --') }}</flux:select.option>
                        @foreach ($this->employees as $emp)
                            <flux:select.option :value="$emp->id">{{ $emp->name }} ({{ ucfirst($emp->wage_type) }})</flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="date" wire:model="date" :label="__('Production Date')" required />
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Finished Goods Made & Raw Materials Consumed') }}</flux:heading>
                        <flux:subheading size="sm">{{ __('Add all products produced by this worker for the day in one entry.') }}</flux:subheading>
                    </div>

                    <flux:button type="button" size="sm" variant="subtle" icon="plus" wire:click="addItem">
                        {{ __('Add Product Item') }}
                    </flux:button>
                </div>

                <div class="flex flex-col gap-4 pt-2">
                    @foreach ($items as $index => $item)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900/70 rounded-xl border border-zinc-200 dark:border-zinc-800 flex flex-col gap-3 relative" wire:key="prod-item-row-{{ $index }}">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">{{ __('Item #:num', ['num' => $index + 1]) }}</span>
                                @if (count($items) > 1)
                                    <flux:button type="button" size="xs" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="removeItem({{ $index }})" />
                                @endif
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                <div class="sm:col-span-8">
                                    <flux:select wire:model="items.{{ $index }}.finished_product_id" :label="__('Finished Product Made')" required>
                                        <flux:select.option value="">{{ __('-- Select Finished Good --') }}</flux:select.option>
                                        @foreach ($this->finishedProducts as $fp)
                                            <flux:select.option :value="$fp->id">{{ $fp->name }} (Current Stock: {{ $fp->stock_level }} {{ $fp->unit }})</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="sm:col-span-4">
                                    <flux:input type="number" step="1" min="1" wire:model="items.{{ $index }}.quantity_produced" :label="__('Qty Produced')" required />
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start pt-1 border-t border-zinc-200/60 dark:border-zinc-800/60">
                                <div class="sm:col-span-8">
                                    <flux:select wire:model="items.{{ $index }}.raw_material_id" :label="__('Raw Material Consumed (Optional)')">
                                        <flux:select.option value="">{{ __('None / N/A') }}</flux:select.option>
                                        @foreach ($this->rawMaterials as $rm)
                                            <flux:select.option :value="$rm->id">{{ $rm->name }} (Stock: {{ $rm->stock_level }} {{ $rm->unit }})</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="sm:col-span-4">
                                    <flux:input type="number" step="1" min="0" wire:model="items.{{ $index }}.raw_quantity_consumed" :label="__('Raw Qty Used')" />
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </flux:card>
        </div>

        <div class="lg:col-span-4 flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <flux:heading size="lg">{{ __('Daily Wages & Summary') }}</flux:heading>

                <div class="flex flex-col gap-3 py-2 border-y border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Product Line Items:') }}</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ count($items) }} {{ __('items') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Total Units Produced:') }}</span>
                        <span class="font-bold text-green-600 dark:text-green-400">{{ $this->totalQuantityProduced }} {{ __('pcs') }}</span>
                    </div>
                </div>

                <div class="flex flex-col gap-1">
                    <flux:input type="number" step="0.01" min="0" wire:model="worker_wage" :label="__('Total Worker Wage for the Day (₹)')" placeholder="e.g. 1128" />
                    <span class="text-xs text-zinc-500">{{ __('This wage will be automatically recorded in the worker’s daily pay ledger.') }}</span>
                </div>

                <flux:textarea wire:model="notes" :label="__('Remarks / Notes')" rows="3" placeholder="e.g. Daily production batch for 5ft, 6ft & 4ft Ghodi" />

                <div class="pt-2">
                    <flux:button type="submit" variant="primary" class="w-full" icon="check">
                        {{ $editingId ? __('Update Production Log') : __('Save Production Log') }}
                    </flux:button>
                </div>
            </flux:card>
        </div>
    </form>
</div>
