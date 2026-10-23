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
     * @var array<int, array{finished_product_id: string|int, quantity_produced: int, raw_material_id: string|int, raw_quantity_consumed: int, calculated_amount?: float, rate_breakdown?: string}>
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
                    $calc = $this->calculateItemWage($item->finished_product_id, (int) $item->quantity_produced);
                    $this->items[] = [
                        'finished_product_id' => (string) $item->finished_product_id,
                        'quantity_produced' => (int) $item->quantity_produced,
                        'raw_material_id' => $item->raw_material_id ? (string) $item->raw_material_id : '',
                        'raw_quantity_consumed' => (int) $item->raw_material_consumed_qty,
                        'calculated_amount' => $calc['amount'],
                        'rate_breakdown' => $calc['breakdown'],
                    ];
                }
            } elseif ($productionLog->finished_product_id) {
                $calc = $this->calculateItemWage($productionLog->finished_product_id, (int) $productionLog->quantity_produced);
                $this->items[] = [
                    'finished_product_id' => (string) $productionLog->finished_product_id,
                    'quantity_produced' => (int) $productionLog->quantity_produced,
                    'raw_material_id' => $productionLog->raw_material_id ? (string) $productionLog->raw_material_id : '',
                    'raw_quantity_consumed' => (int) $productionLog->raw_material_consumed_qty,
                    'calculated_amount' => $calc['amount'],
                    'rate_breakdown' => $calc['breakdown'],
                ];
            } else {
                $this->addItem();
            }
        } else {
            $this->authorize('create', ProductionLog::class);
            $this->date = now()->toDateString();
            $this->worker_wage = 0.0;
            $this->items = [
                [
                    'finished_product_id' => '',
                    'quantity_produced' => 1,
                    'raw_material_id' => '',
                    'raw_quantity_consumed' => 0,
                    'calculated_amount' => 0.0,
                    'rate_breakdown' => '',
                ],
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

    /**
     * Calculate piece-rate wage and formula breakdown for a given finished product and quantity.
     *
     * Example:
     * - 5 feet Ghodi Medium × 10 qty: 5 * 10 * 11 = 550.00 (rate: ₹11/ft)
     * - 5 feet Ghodi Heavy × 2 qty: 5 * 2 * 12 = 120.00 (rate: ₹12/ft)
     * - Siddi (12ft - 10 dandas) × 1 qty: 10 * 1 * 4 = 40.00 (rate: ₹4/danda)
     * - Challi × 2 qty: 2 * 20 = 40.00 (rate: ₹20/challi)
     *
     * @return array{amount: float, rate: float, feet?: float, dandas?: int, unit_cost: float, item_type: string, breakdown: string, rate_label: string}
     */
    public function calculateItemWage(string|int $finishedProductId, int|float $qty): array
    {
        if (empty($finishedProductId) || (int) $qty <= 0) {
            return [
                'amount' => 0.0,
                'rate' => 0.0,
                'unit_cost' => 0.0,
                'item_type' => 'unknown',
                'breakdown' => '',
                'rate_label' => '',
            ];
        }

        $product = Product::find($finishedProductId);
        if (! $product) {
            return [
                'amount' => 0.0,
                'rate' => 0.0,
                'unit_cost' => 0.0,
                'item_type' => 'unknown',
                'breakdown' => '',
                'rate_label' => '',
            ];
        }

        $employee = $this->employee_id ? Employee::find($this->employee_id) : null;
        $medRate = $employee && (float) $employee->ghodi_medium_rate > 0 ? (float) $employee->ghodi_medium_rate : 11.00;
        $heavyRate = $employee && (float) $employee->ghodi_heavy_rate > 0 ? (float) $employee->ghodi_heavy_rate : 12.00;
        $dandaRate = $employee && (float) $employee->siddi_danda_rate > 0 ? (float) $employee->siddi_danda_rate : 4.00;
        $challiRate = $employee && (float) $employee->challi_rate > 0 ? (float) $employee->challi_rate : 20.00;

        $name = strtolower($product->name);
        $qty = max(1, (int) $qty);

        // 1. Detect Ghodi (Medium or Heavy)
        if (str_contains($name, 'ghodi') || str_contains($name, 'trestle')) {
            $feet = 5.0; // standard default ghodi height
            if (preg_match('/(\d+(?:\.\d+)?)\s*(?:feet|foot|ft|\'|f\b)/i', $name, $matches)) {
                $feet = (float) $matches[1];
            } elseif (preg_match('/(?:ghodi|heavy|medium)\D*(\d+(?:\.\d+)?)/i', $name, $matches)) {
                $val = (float) $matches[1];
                if ($val >= 2 && $val <= 25) {
                    $feet = $val;
                }
            } elseif (preg_match('/(\d+(?:\.\d+)?)\D*(?:ghodi)/i', $name, $matches)) {
                $val = (float) $matches[1];
                if ($val >= 2 && $val <= 25) {
                    $feet = $val;
                }
            }

            $isHeavy = str_contains($name, 'heavy') || str_contains($name, 'bhari') || str_contains($name, 'bhaari');
            $rate = $isHeavy ? $heavyRate : $medRate;
            $unitCost = $feet * $rate;
            $amount = $feet * $qty * $rate;
            $breakdown = "{$feet}ft × {$qty}pcs × ₹{$rate} = ₹" . number_format($amount, 2);

            return [
                'amount' => round($amount, 2),
                'rate' => $rate,
                'feet' => $feet,
                'unit_cost' => $unitCost,
                'item_type' => $isHeavy ? 'ghodi_heavy' : 'ghodi_medium',
                'breakdown' => $breakdown,
                'rate_label' => "₹{$rate}/ft",
            ];
        }

        // 2. Detect Siddi / Ladder
        if (str_contains($name, 'siddi') || str_contains($name, 'ladder') || str_contains($name, 'seedhi') || str_contains($name, 'sidhi')) {
            $feet = 12;
            if (preg_match('/(\d+)\s*(?:feet|foot|ft|\')/i', $name, $matches)) {
                $feet = (int) $matches[1];
            } elseif (preg_match('/25/', $name)) {
                $feet = 25;
            }

            // Hardcode check: 25 feet Siddi cost of pay to employee is fixed at Rs 100 per piece
            if ($feet === 25 || str_contains($name, '25')) {
                $unitCost = 100.00;
                $amount = $qty * $unitCost;
                $breakdown = "25ft Siddi: {$qty}pcs × ₹100 = ₹" . number_format($amount, 2);

                return [
                    'amount' => round($amount, 2),
                    'rate' => 100.00,
                    'feet' => 25,
                    'unit_cost' => $unitCost,
                    'item_type' => 'siddi_25ft',
                    'breakdown' => $breakdown,
                    'rate_label' => '₹100/pc (Fixed)',
                ];
            }

            $map = [
                6 => 5,
                10 => 8,
                12 => 10,
                14 => 12,
                15 => 13,
                16 => 14,
                18 => 16,
                20 => 18,
                25 => 22,
            ];
            $dandas = $map[$feet] ?? max(1, (int) round($feet * 0.85));
            if (preg_match('/(\d+)\s*(?:danda|step)/i', $name, $matches)) {
                $dandas = (int) $matches[1];
            }

            $rate = $dandaRate;
            $unitCost = $dandas * $rate;
            $amount = $dandas * $qty * $rate;
            $breakdown = "{$dandas} dandas × {$qty}pcs × ₹{$rate} = ₹" . number_format($amount, 2);

            return [
                'amount' => round($amount, 2),
                'rate' => $rate,
                'feet' => $feet,
                'dandas' => $dandas,
                'unit_cost' => $unitCost,
                'item_type' => 'siddi',
                'breakdown' => $breakdown,
                'rate_label' => "₹{$rate}/danda",
            ];
        }

        // 3. Detect Challi
        if (str_contains($name, 'challi') || str_contains($name, 'chaali') || str_contains($name, 'jali') || str_contains($name, 'jaali')) {
            $rate = $challiRate;
            $amount = $qty * $rate;
            $breakdown = "{$qty}pcs × ₹{$rate} = ₹" . number_format($amount, 2);

            return [
                'amount' => round($amount, 2),
                'rate' => $rate,
                'unit_cost' => $rate,
                'item_type' => 'challi',
                'breakdown' => $breakdown,
                'rate_label' => "₹{$rate}/pc",
            ];
        }

        // 4. Other finished goods
        return [
            'amount' => 0.0,
            'rate' => 0.0,
            'unit_cost' => 0.0,
            'item_type' => 'other',
            'breakdown' => '',
            'rate_label' => '',
        ];
    }

    public function updated($property): void
    {
        if ($property === 'employee_id') {
            $this->recalculateWages();
            return;
        }

        if (str_starts_with($property, 'items.')) {
            if (str_ends_with($property, '.finished_product_id') || str_ends_with($property, '.quantity_produced')) {
                preg_match('/items\.(\d+)\./', $property, $matches);
                if (isset($matches[1])) {
                    $idx = (int) $matches[1];
                    $this->recalculateItem($idx);
                }
                $this->syncTotalWage();
            } elseif (str_ends_with($property, '.calculated_amount')) {
                $this->syncTotalWage();
            }
        }
    }

    public function recalculateItem(int $index): void
    {
        if (! isset($this->items[$index])) {
            return;
        }

        $prodId = $this->items[$index]['finished_product_id'] ?? '';
        $qty = (int) ($this->items[$index]['quantity_produced'] ?? 1);

        $calc = $this->calculateItemWage($prodId, $qty);
        $this->items[$index]['calculated_amount'] = $calc['amount'];
        $this->items[$index]['rate_breakdown'] = $calc['breakdown'];
    }

    public function recalculateWages(): void
    {
        foreach ($this->items as $index => $item) {
            $this->recalculateItem($index);
        }
        $this->syncTotalWage();
    }

    public function syncTotalWage(): void
    {
        $total = 0.0;
        foreach ($this->items as $item) {
            $total += (float) ($item['calculated_amount'] ?? 0.0);
        }
        $this->worker_wage = round($total, 2);
    }

    public function resetWageToCalculated(): void
    {
        $this->syncTotalWage();
        Flux::toast(variant: 'info', text: __('Worker wage reset to calculated piece-rate total: ₹:amt', ['amt' => number_format($this->worker_wage, 2)]));
    }

    public function addItem(): void
    {
        $this->items[] = [
            'finished_product_id' => '',
            'quantity_produced' => 1,
            'raw_material_id' => '',
            'raw_quantity_consumed' => 0,
            'calculated_amount' => 0.0,
            'rate_breakdown' => '',
        ];
    }

    public function removeItem(int $index): void
    {
        if (count($this->items) > 1) {
            unset($this->items[$index]);
            $this->items = array_values($this->items);
            $this->syncTotalWage();
        }
    }

    #[Computed]
    public function totalQuantityProduced(): int
    {
        return array_reduce($this->items, function ($carry, $item) {
            return $carry + max(0, (int) ($item['quantity_produced'] ?? 0));
        }, 0);
    }

    #[Computed]
    public function totalCalculatedWage(): float
    {
        return (float) array_reduce($this->items, function ($carry, $item) {
            return $carry + (float) ($item['calculated_amount'] ?? 0.0);
        }, 0.0);
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
            'items.*.calculated_amount' => ['nullable', 'numeric', 'min:0'],
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
                    <flux:select wire:model.live="employee_id" :label="__('Worker / Employee')" required>
                        <flux:select.option value="">{{ __('-- Select Worker (e.g. Talib Khan) --') }}</flux:select.option>
                        @foreach ($this->employees as $emp)
                            <flux:select.option :value="$emp->id">
                                {{ $emp->name }}
                                @if ($emp->ghodi_medium_rate || $emp->ghodi_heavy_rate)
                                    (₹{{ number_format((float) ($emp->ghodi_medium_rate ?: 11), 0) }} / ₹{{ number_format((float) ($emp->ghodi_heavy_rate ?: 12), 0) }}/ft)
                                @endif
                            </flux:select.option>
                        @endforeach
                    </flux:select>

                    <flux:input type="date" wire:model="date" :label="__('Production Date')" required />
                </div>
            </flux:card>

            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg">{{ __('Finished Goods Made & Raw Materials Consumed') }}</flux:heading>
                        <flux:subheading size="sm">{{ __('Add all products produced by this worker for the day in one entry. Amount is calculated automatically.') }}</flux:subheading>
                    </div>

                    <flux:button type="button" size="sm" variant="subtle" icon="plus" wire:click="addItem">
                        {{ __('Add Product Item') }}
                    </flux:button>
                </div>

                <div class="flex flex-col gap-4 pt-2">
                    @foreach ($items as $index => $item)
                        <div class="p-4 bg-zinc-50 dark:bg-zinc-900/70 rounded-xl border border-zinc-200 dark:border-zinc-800 flex flex-col gap-3 relative" wire:key="prod-item-row-{{ $index }}">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="text-xs font-bold text-indigo-600 dark:text-indigo-400 uppercase tracking-wider">{{ __('Item #:num', ['num' => $index + 1]) }}</span>
                                    @if (! empty($item['calculated_amount']) && (float) $item['calculated_amount'] > 0)
                                        <span class="text-xs font-bold text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/60 px-2 py-0.5 rounded-full border border-emerald-200 dark:border-emerald-800">
                                            ₹{{ number_format((float) $item['calculated_amount'], 2) }}
                                        </span>
                                    @endif
                                </div>
                                @if (count($items) > 1)
                                    <flux:button type="button" size="xs" variant="ghost" icon="trash" class="text-red-500 hover:text-red-600" wire:click="removeItem({{ $index }})" />
                                @endif
                            </div>

                            {{-- Main Row with Product, Quantity, and the Extra Calculated Amount Column --}}
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                <div class="sm:col-span-5">
                                    <flux:select wire:model.live="items.{{ $index }}.finished_product_id" :label="__('Finished Product Made')" required>
                                        <flux:select.option value="">{{ __('-- Select Finished Good --') }}</flux:select.option>
                                        @foreach ($this->finishedProducts as $fp)
                                            <flux:select.option :value="$fp->id">{{ $fp->name }} (Current Stock: {{ $fp->stock_level }} {{ $fp->unit }})</flux:select.option>
                                        @endforeach
                                    </flux:select>
                                </div>

                                <div class="sm:col-span-3">
                                    <flux:input type="number" step="1" min="1" wire:model.live="items.{{ $index }}.quantity_produced" :label="__('Qty Produced')" required />
                                </div>

                                {{-- Extra Column: Amount for Produced Goods (Calculated) --}}
                                <div class="sm:col-span-4">
                                    <flux:input
                                        type="number"
                                        step="0.01"
                                        min="0"
                                        wire:model.live="items.{{ $index }}.calculated_amount"
                                        :label="__('Produced Amount (₹)')"
                                        placeholder="0.00"
                                    />
                                    @if (! empty($item['rate_breakdown']))
                                        <div class="mt-1 flex items-center justify-between text-[11px] text-zinc-500">
                                            <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold truncate" title="{{ $item['rate_breakdown'] }}">
                                                🧮 {{ $item['rate_breakdown'] }}
                                            </span>
                                            <button type="button" wire:click="recalculateItem({{ $index }})" class="text-indigo-600 dark:text-indigo-400 hover:underline shrink-0 ml-1 text-xs" title="{{ __('Recalculate from rates') }}">
                                                ↺ {{ __('Reset') }}
                                            </button>
                                        </div>
                                    @endif
                                </div>
                            </div>

                            {{-- Raw Material Consumption Row --}}
                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start pt-2 border-t border-zinc-200/60 dark:border-zinc-800/60">
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

        {{-- Right Sidebar: Daily Wages & Summary --}}
        <div class="lg:col-span-4 flex flex-col gap-6">
            <flux:card class="flex flex-col gap-4">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">{{ __('Daily Wages & Summary') }}</flux:heading>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-950 text-emerald-700 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        {{ __('Piece Rates Active') }}
                    </span>
                </div>

                <div class="flex flex-col gap-3 py-2 border-y border-zinc-200 dark:border-zinc-800">
                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Product Line Items:') }}</span>
                        <span class="font-semibold text-zinc-800 dark:text-zinc-200">{{ count($items) }} {{ __('items') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400">
                        <span>{{ __('Total Units Produced:') }}</span>
                        <span class="font-bold text-green-600 dark:text-green-400">{{ $this->totalQuantityProduced }} {{ __('pcs') }}</span>
                    </div>

                    <div class="flex items-center justify-between text-sm text-zinc-600 dark:text-zinc-400 pt-2 border-t border-zinc-100 dark:border-zinc-800">
                        <span>{{ __('Produced Goods Total:') }}</span>
                        <span class="font-bold text-emerald-600 dark:text-emerald-400 text-base">₹{{ number_format($this->totalCalculatedWage, 2) }}</span>
                    </div>
                </div>

                @if (count($items) > 0 && $this->totalCalculatedWage > 0)
                    <div class="p-3 bg-zinc-50 dark:bg-zinc-900/60 rounded-xl border border-zinc-200 dark:border-zinc-800 flex flex-col gap-2 text-xs">
                        <div class="flex items-center justify-between">
                            <span class="font-bold text-zinc-700 dark:text-zinc-300 uppercase tracking-wider text-[10px]">
                                {{ __('Wage Breakdown by Item') }}
                            </span>
                            <span class="text-[10px] text-zinc-400 font-mono">{{ count($items) }} {{ __('item(s)') }}</span>
                        </div>
                        @foreach ($items as $idx => $it)
                            @if (! empty($it['rate_breakdown']))
                                <div class="flex items-center justify-between text-zinc-600 dark:text-zinc-400 pt-1 border-t border-zinc-200/50 dark:border-zinc-800/50">
                                    <span class="font-medium text-zinc-700 dark:text-zinc-300">{{ __('Item #:n', ['n' => $idx + 1]) }}</span>
                                    <span class="font-mono text-emerald-600 dark:text-emerald-400 font-semibold">{{ $it['rate_breakdown'] }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                @endif

                <div class="flex flex-col gap-1">
                    <div class="flex items-center justify-between">
                        <flux:label>{{ __('Total Worker Wage for the Day (₹)') }}</flux:label>
                        @if ($this->totalCalculatedWage > 0 && abs((float) $worker_wage - $this->totalCalculatedWage) > 0.01)
                            <button type="button" wire:click="resetWageToCalculated" class="text-[11px] font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ __('Reset to ₹:amt', ['amt' => number_format($this->totalCalculatedWage, 2)]) }}
                            </button>
                        @endif
                    </div>
                    <flux:input type="number" step="0.01" min="0" wire:model="worker_wage" placeholder="e.g. 1128" />
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
