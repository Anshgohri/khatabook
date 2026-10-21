<?php

use App\Models\Employee;
use Flux\Flux;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Employee Piece Rates')] class extends Component {
    use WithPagination;

    #[Url]
    public string $search = '';

    // Modal State for Editing Rates
    public bool $showEditModal = false;
    public ?int $editingEmployeeId = null;
    public string $editingEmployeeName = '';
    public float $ghodi_medium_rate = 11.00;
    public float $ghodi_heavy_rate = 12.00;
    public float $siddi_danda_rate = 4.00;
    public float $challi_rate = 20.00;
    public float $default_daily_rate = 0.00;

    // Interactive Rate Calculator State
    public ?int $calcEmployeeId = null;
    public string $calcItemType = 'ghodi_medium'; // ghodi_medium, ghodi_heavy, siddi, challi
    public float $calcGhodiFeet = 5.0;
    public int $calcSiddiFeet = 12;
    public int $calcSiddiDandas = 10;
    public int $calcQuantity = 1;

    public function mount(): void
    {
        $this->authorize('viewAny', Employee::class);
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
            ->when(! $user->isManager() && ! $user->isAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->when($this->search, function ($q) {
                $q->where(function ($sub) {
                    $sub->where('name', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%")
                        ->orWhere('phone', \App\Providers\AppServiceProvider::likeOperator(), "%{$this->search}%");
                });
            })
            ->latest()
            ->paginate(15);
    }

    public function editRates(int $employeeId): void
    {
        $employee = Employee::findOrFail($employeeId);
        $this->authorize('update', $employee);

        $this->editingEmployeeId = $employee->id;
        $this->editingEmployeeName = $employee->name;
        $this->ghodi_medium_rate = (float) ($employee->ghodi_medium_rate ?: 11.00);
        $this->ghodi_heavy_rate = (float) ($employee->ghodi_heavy_rate ?: 12.00);
        $this->siddi_danda_rate = (float) ($employee->siddi_danda_rate ?: 4.00);
        $this->challi_rate = (float) ($employee->challi_rate ?: 20.00);
        $this->default_daily_rate = (float) $employee->default_daily_rate;
        $this->showEditModal = true;
    }

    public function resetToDefaults(): void
    {
        $this->ghodi_medium_rate = 11.00;
        $this->ghodi_heavy_rate = 12.00;
        $this->siddi_danda_rate = 4.00;
        $this->challi_rate = 20.00;
        Flux::toast(variant: 'info', text: __('Reset to standard rates (₹11/ft, ₹12/ft, ₹4/danda, ₹20/challi).'));
    }

    public function saveRates(): void
    {
        $employee = Employee::findOrFail($this->editingEmployeeId);
        $this->authorize('update', $employee);

        $validated = $this->validate([
            'ghodi_medium_rate' => ['required', 'numeric', 'min:0'],
            'ghodi_heavy_rate' => ['required', 'numeric', 'min:0'],
            'siddi_danda_rate' => ['required', 'numeric', 'min:0'],
            'challi_rate' => ['required', 'numeric', 'min:0'],
            'default_daily_rate' => ['required', 'numeric', 'min:0'],
        ]);

        $employee->update($validated);

        $this->showEditModal = false;
        unset($this->employees);
        Flux::toast(variant: 'success', text: __(':name\'s piece rates updated successfully.', ['name' => $employee->name]));
    }

    public function updatedCalcSiddiFeet($value): void
    {
        // Auto-map standard siddi heights to standard danda counts
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

        $this->calcSiddiDandas = $map[(int) $value] ?? max(1, (int) round(((int) $value * 0.85)));
    }

    #[Computed]
    public function simulatedCost(): array
    {
        $employee = $this->calcEmployeeId ? Employee::find($this->calcEmployeeId) : null;

        $medRate = $employee ? (float) $employee->ghodi_medium_rate : 11.00;
        $heavyRate = $employee ? (float) $employee->ghodi_heavy_rate : 12.00;
        $dandaRate = $employee ? (float) $employee->siddi_danda_rate : 4.00;
        $challiRate = $employee ? (float) $employee->challi_rate : 20.00;

        $qty = max(1, $this->calcQuantity);

        switch ($this->calcItemType) {
            case 'ghodi_medium':
                $feet = max(1.0, (float) $this->calcGhodiFeet);
                $unitCost = $feet * $medRate;
                $totalCost = $unitCost * $qty;
                return [
                    'label' => __('Medium Ghodi (:feet ft)', ['feet' => $feet]),
                    'rate_label' => "₹{$medRate} / ft",
                    'formula' => "{$feet} ft × ₹{$medRate}/ft × {$qty} pcs",
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];

            case 'ghodi_heavy':
                $feet = max(1.0, (float) $this->calcGhodiFeet);
                $unitCost = $feet * $heavyRate;
                $totalCost = $unitCost * $qty;
                return [
                    'label' => __('Heavy Ghodi (:feet ft)', ['feet' => $feet]),
                    'rate_label' => "₹{$heavyRate} / ft",
                    'formula' => "{$feet} ft × ₹{$heavyRate}/ft × {$qty} pcs",
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];

            case 'siddi':
                $dandas = max(1, $this->calcSiddiDandas);
                $unitCost = $dandas * $dandaRate;
                $totalCost = $unitCost * $qty;
                return [
                    'label' => __('Siddi (:feet ft / :dandas Dandas)', ['feet' => $this->calcSiddiFeet, 'dandas' => $dandas]),
                    'rate_label' => "₹{$dandaRate} / danda",
                    'formula' => "{$dandas} dandas × ₹{$dandaRate}/danda × {$qty} pcs",
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];

            case 'challi':
            default:
                $unitCost = $challiRate;
                $totalCost = $unitCost * $qty;
                return [
                    'label' => __('Challi'),
                    'rate_label' => "₹{$challiRate} / challi",
                    'formula' => "{$qty} challi × ₹{$challiRate}/challi",
                    'unit_cost' => $unitCost,
                    'total_cost' => $totalCost,
                ];
        }
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Employee Piece Rates') }}</flux:heading>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/40">
                    🛠️ {{ __('Ghodi, Siddi & Challi') }}
                </span>
            </div>
            <flux:subheading>
                {{ __('Manage manufacturing piece-rates paid to employees for Ghodi (Medium & Heavy per ft), Siddi (per danda), and Challi.') }}
            </flux:subheading>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="subtle" icon="user-group" :href="route('employees')" wire:navigate>
                {{ __('Employees List') }}
            </flux:button>
            <flux:button variant="subtle" icon="wrench-screwdriver" :href="route('production')" wire:navigate>
                {{ __('Production') }}
            </flux:button>
        </div>
    </div>

    <!-- Quick Rate Reference Cards -->
    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Card 1: Medium Ghodi -->
        <flux:card class="flex flex-col justify-between gap-3 border-l-4 border-l-blue-500 bg-blue-50/20 dark:bg-blue-950/10">
            <div>
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="font-semibold text-blue-800 dark:text-blue-300">{{ __('Medium Ghodi') }}</flux:text>
                    <span class="text-xs px-2 py-0.5 rounded bg-blue-500/10 text-blue-600 dark:text-blue-400 font-bold">₹11 / ft</span>
                </div>
                <flux:heading size="xl" class="mt-1 text-blue-600 dark:text-blue-400">₹11.00 <span class="text-xs font-normal text-zinc-500">/ foot</span></flux:heading>
                <flux:text size="xs" class="text-zinc-600 dark:text-zinc-400 mt-1">
                    {{ __('Standard labor rate: Length (ft) × ₹11') }}
                </flux:text>
            </div>
            <div class="text-[11px] text-zinc-500 border-t border-zinc-200 dark:border-zinc-800 pt-2 flex flex-wrap gap-x-2 gap-y-0.5">
                <span>4ft = <strong>₹44</strong></span>
                <span>•</span>
                <span>5ft = <strong>₹55</strong></span>
                <span>•</span>
                <span>6ft = <strong>₹66</strong></span>
                <span>•</span>
                <span>7ft = <strong>₹77</strong></span>
            </div>
        </flux:card>

        <!-- Card 2: Heavy Ghodi -->
        <flux:card class="flex flex-col justify-between gap-3 border-l-4 border-l-purple-500 bg-purple-50/20 dark:bg-purple-950/10">
            <div>
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="font-semibold text-purple-800 dark:text-purple-300">{{ __('Heavy Ghodi') }}</flux:text>
                    <span class="text-xs px-2 py-0.5 rounded bg-purple-500/10 text-purple-600 dark:text-purple-400 font-bold">₹12 / ft</span>
                </div>
                <flux:heading size="xl" class="mt-1 text-purple-600 dark:text-purple-400">₹12.00 <span class="text-xs font-normal text-zinc-500">/ foot</span></flux:heading>
                <flux:text size="xs" class="text-zinc-600 dark:text-zinc-400 mt-1">
                    {{ __('Standard labor rate: Length (ft) × ₹12') }}
                </flux:text>
            </div>
            <div class="text-[11px] text-zinc-500 border-t border-zinc-200 dark:border-zinc-800 pt-2 flex flex-wrap gap-x-2 gap-y-0.5">
                <span>4ft = <strong>₹48</strong></span>
                <span>•</span>
                <span>5ft = <strong>₹60</strong></span>
                <span>•</span>
                <span>6ft = <strong>₹72</strong></span>
                <span>•</span>
                <span>7ft = <strong>₹84</strong></span>
            </div>
        </flux:card>

        <!-- Card 3: Siddi (Per Danda) -->
        <flux:card class="flex flex-col justify-between gap-3 border-l-4 border-l-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10">
            <div>
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="font-semibold text-emerald-800 dark:text-emerald-300">{{ __('Siddi (Ladder)') }}</flux:text>
                    <span class="text-xs px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 font-bold">₹4 / danda</span>
                </div>
                <flux:heading size="xl" class="mt-1 text-emerald-600 dark:text-emerald-400">₹4.00 <span class="text-xs font-normal text-zinc-500">/ danda</span></flux:heading>
                <flux:text size="xs" class="text-zinc-600 dark:text-zinc-400 mt-1">
                    {{ __('Formula: Total Dandas (steps) × ₹4') }}
                </flux:text>
            </div>
            <div class="text-[11px] text-zinc-500 border-t border-zinc-200 dark:border-zinc-800 pt-2 flex flex-wrap gap-x-2 gap-y-0.5">
                <span>12ft (10d) = <strong>₹40</strong></span>
                <span>•</span>
                <span>15ft (13d) = <strong>₹52</strong></span>
                <span>•</span>
                <span>20ft (18d) = <strong>₹72</strong></span>
            </div>
        </flux:card>

        <!-- Card 4: Challi -->
        <flux:card class="flex flex-col justify-between gap-3 border-l-4 border-l-amber-500 bg-amber-50/20 dark:bg-amber-950/10">
            <div>
                <div class="flex items-center justify-between">
                    <flux:text size="sm" class="font-semibold text-amber-800 dark:text-amber-300">{{ __('Challi (Piece Rate)') }}</flux:text>
                    <span class="text-xs px-2 py-0.5 rounded bg-amber-500/10 text-amber-600 dark:text-amber-400 font-bold">₹20 / pc</span>
                </div>
                <flux:heading size="xl" class="mt-1 text-amber-600 dark:text-amber-400">₹20.00 <span class="text-xs font-normal text-zinc-500">/ challi</span></flux:heading>
                <flux:text size="xs" class="text-zinc-600 dark:text-zinc-400 mt-1">
                    {{ __('Flat rate per piece of finished challi') }}
                </flux:text>
            </div>
            <div class="text-[11px] text-zinc-500 border-t border-zinc-200 dark:border-zinc-800 pt-2 flex flex-wrap gap-x-2 gap-y-0.5">
                <span>5 pcs = <strong>₹100</strong></span>
                <span>•</span>
                <span>10 pcs = <strong>₹200</strong></span>
                <span>•</span>
                <span>25 pcs = <strong>₹500</strong></span>
            </div>
        </flux:card>
    </div>

    <!-- Live Rate Simulator / Calculator -->
    <flux:card class="p-5 border border-zinc-200 dark:border-zinc-700 bg-zinc-50/50 dark:bg-zinc-900/50">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4 mb-4">
            <div>
                <flux:heading size="md">{{ __('Interactive Wage / Cost Calculator') }}</flux:heading>
                <flux:text size="xs" class="text-zinc-500">{{ __('Simulate employee earnings based on piece rates and quantity.') }}</flux:text>
            </div>
            <div class="w-full sm:w-64">
                <flux:select wire:model.live="calcEmployeeId" :placeholder="__('Standard Store Rates')">
                    <flux:select.option value="">{{ __('Standard Store Defaults') }}</flux:select.option>
                    @foreach ($this->employees as $emp)
                        <flux:select.option value="{{ $emp->id }}">{{ $emp->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4 items-end">
            <!-- Item Type -->
            <flux:select wire:model.live="calcItemType" :label="__('Manufactured Item')">
                <flux:select.option value="ghodi_medium">{{ __('Medium Ghodi (घोड़ी मध्यम)') }}</flux:select.option>
                <flux:select.option value="ghodi_heavy">{{ __('Heavy Ghodi (घोड़ी भारी)') }}</flux:select.option>
                <flux:select.option value="siddi">{{ __('Siddi (सीढ़ी / Ladder)') }}</flux:select.option>
                <flux:select.option value="challi">{{ __('Challi (छल्ली)') }}</flux:select.option>
            </flux:select>

            <!-- Conditional Dimensions based on Type -->
            @if ($calcItemType === 'ghodi_medium' || $calcItemType === 'ghodi_heavy')
                <flux:select wire:model.live="calcGhodiFeet" :label="__('Ghodi Height / Feet')">
                    <flux:select.option value="3">3 Feet</flux:select.option>
                    <flux:select.option value="4">4 Feet</flux:select.option>
                    <flux:select.option value="5">5 Feet</flux:select.option>
                    <flux:select.option value="6">6 Feet</flux:select.option>
                    <flux:select.option value="7">7 Feet</flux:select.option>
                    <flux:select.option value="8">8 Feet</flux:select.option>
                </flux:select>
            @elseif ($calcItemType === 'siddi')
                <flux:select wire:model.live="calcSiddiFeet" :label="__('Siddi Size')">
                    <flux:select.option value="6">6 Feet (5 Dandas)</flux:select.option>
                    <flux:select.option value="10">10 Feet (8 Dandas)</flux:select.option>
                    <flux:select.option value="12">12 Feet (10 Dandas)</flux:select.option>
                    <flux:select.option value="14">14 Feet (12 Dandas)</flux:select.option>
                    <flux:select.option value="15">15 Feet (13 Dandas)</flux:select.option>
                    <flux:select.option value="16">16 Feet (14 Dandas)</flux:select.option>
                    <flux:select.option value="18">18 Feet (16 Dandas)</flux:select.option>
                    <flux:select.option value="20">20 Feet (18 Dandas)</flux:select.option>
                    <flux:select.option value="25">25 Feet (22 Dandas)</flux:select.option>
                </flux:select>
            @else
                <div>
                    <flux:input :label="__('Rate Applied')" value="₹20.00 / piece" disabled />
                </div>
            @endif

            <!-- Quantity -->
            <flux:input type="number" min="1" step="1" wire:model.live="calcQuantity" :label="__('Quantity Produced')" />

            <!-- Computed Output Card -->
            <div class="rounded-lg p-3 bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 shadow-xs flex flex-col justify-center">
                <span class="text-[11px] text-zinc-500 font-medium uppercase tracking-wider">{{ __('Total Giving Cost') }}</span>
                <span class="text-xl font-black text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->simulatedCost['total_cost'], 2) }}</span>
                <span class="text-[10px] text-zinc-500 truncate" title="{{ $this->simulatedCost['formula'] }}">{{ $this->simulatedCost['formula'] }}</span>
            </div>
        </div>
    </flux:card>

    <!-- Siddi Standards Reference Details -->
    <details class="group rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-800/50 p-4 transition">
        <summary class="flex cursor-pointer items-center justify-between font-semibold text-sm text-zinc-800 dark:text-zinc-200">
            <span class="flex items-center gap-2">
                <span>🪜</span>
                <span>{{ __('Standard Siddi Danda & Labor Cost Schedule (₹4 / Danda)') }}</span>
            </span>
            <span class="text-xs text-indigo-600 dark:text-indigo-400 group-open:rotate-180 transition-transform">▼</span>
        </summary>
        <div class="mt-4 pt-3 border-t border-zinc-100 dark:border-zinc-700 grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 text-xs">
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">10 Feet</div>
                <div class="text-zinc-500">8 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹32.00</div>
            </div>
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-emerald-300 dark:border-emerald-700 text-center shadow-xs">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">12 Feet</div>
                <div class="text-emerald-600 dark:text-emerald-400 font-medium">10 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹40.00</div>
            </div>
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">14 Feet</div>
                <div class="text-zinc-500">12 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹48.00</div>
            </div>
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-emerald-300 dark:border-emerald-700 text-center shadow-xs">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">15 Feet</div>
                <div class="text-emerald-600 dark:text-emerald-400 font-medium">13 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹52.00</div>
            </div>
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">18 Feet</div>
                <div class="text-zinc-500">16 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹64.00</div>
            </div>
            <div class="p-2.5 rounded-lg bg-zinc-50 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 text-center">
                <div class="font-bold text-zinc-700 dark:text-zinc-300">20 Feet</div>
                <div class="text-zinc-500">18 Dandas</div>
                <div class="font-black text-emerald-600 dark:text-emerald-400 mt-1">₹72.00</div>
            </div>
        </div>
    </details>

    <!-- Employee Rates Table Section -->
    <div class="flex flex-col gap-4">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-2">
                <flux:heading size="lg">{{ __('Individual Employee Rate Cards') }}</flux:heading>
                <flux:badge size="sm" color="zinc">{{ $this->employees->total() }} {{ __('Employees') }}</flux:badge>
            </div>
            <div class="w-full sm:w-72">
                <flux:input wire:model.live.debounce.400ms="search" :placeholder="__('Search employee name or phone...')" icon="magnifying-glass" />
            </div>
        </div>

        <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <flux:table :paginate="$this->employees">
                <flux:table.columns>
                    <flux:table.column>{{ __('Employee Name') }}</flux:table.column>
                    <flux:table.column>{{ __('Status') }}</flux:table.column>
                    <flux:table.column>{{ __('Medium Ghodi') }}</flux:table.column>
                    <flux:table.column>{{ __('Heavy Ghodi') }}</flux:table.column>
                    <flux:table.column>{{ __('Siddi / Danda') }}</flux:table.column>
                    <flux:table.column>{{ __('Challi') }}</flux:table.column>
                    <flux:table.column>{{ __('Daily Base Wage') }}</flux:table.column>
                    <flux:table.column></flux:table.column>
                </flux:table.columns>

                <flux:table.rows>
                    @forelse ($this->employees as $emp)
                    <flux:table.row wire:key="emp-rate-{{ $emp->id }}">
                        <flux:table.cell class="font-medium">
                            <div class="flex flex-col">
                                <a href="{{ route('employees.show', $emp->id) }}" wire:navigate class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                    {{ $emp->name }}
                                </a>
                                @if ($emp->phone)
                                    <span class="text-xs text-zinc-500 font-normal">📞 {{ $emp->phone }}</span>
                                @endif
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <flux:badge size="sm" :color="$emp->status === 'active' ? 'green' : 'zinc'">
                                {{ ucfirst($emp->status) }}
                            </flux:badge>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-baseline gap-1">
                                <span class="font-bold text-blue-600 dark:text-blue-400">₹{{ number_format((float) ($emp->ghodi_medium_rate ?: 11.00), 2) }}</span>
                                <span class="text-[11px] text-zinc-500">/ ft</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-baseline gap-1">
                                <span class="font-bold text-purple-600 dark:text-purple-400">₹{{ number_format((float) ($emp->ghodi_heavy_rate ?: 12.00), 2) }}</span>
                                <span class="text-[11px] text-zinc-500">/ ft</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-baseline gap-1">
                                <span class="font-bold text-emerald-600 dark:text-emerald-400">₹{{ number_format((float) ($emp->siddi_danda_rate ?: 4.00), 2) }}</span>
                                <span class="text-[11px] text-zinc-500">/ danda</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-baseline gap-1">
                                <span class="font-bold text-amber-600 dark:text-amber-400">₹{{ number_format((float) ($emp->challi_rate ?: 20.00), 2) }}</span>
                                <span class="text-[11px] text-zinc-500">/ pc</span>
                            </div>
                        </flux:table.cell>

                        <flux:table.cell>
                            <span class="text-xs font-semibold text-zinc-700 dark:text-zinc-300">
                                ₹{{ number_format((float) $emp->default_daily_rate, 2) }} / day
                            </span>
                        </flux:table.cell>

                        <flux:table.cell>
                            <div class="flex items-center gap-2 justify-end">
                                @can('update', $emp)
                                <flux:button size="sm" variant="primary" icon="pencil" wire:click="editRates({{ $emp->id }})">
                                    {{ __('Edit Rates') }}
                                </flux:button>
                                @endcan
                                <flux:button size="sm" variant="subtle" icon="eye" :href="route('employees.show', $emp->id)" wire:navigate title="{{ __('View Ledger') }}">
                                    {{ __('Ledger') }}
                                </flux:button>
                            </div>
                        </flux:table.cell>
                    </flux:table.row>
                    @empty
                    <flux:table.row>
                        <flux:table.cell colspan="8" class="text-center text-zinc-500 py-6">
                            {{ __('No employees found.') }}
                        </flux:table.cell>
                    </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </div>
    </div>

    <!-- Edit Rates Modal -->
    <flux:modal wire:model.self="showEditModal" class="md:w-[500px]">
        <div class="flex flex-col gap-6">
            <div>
                <flux:heading size="lg">{{ __('Configure Piece Rates') }}</flux:heading>
                <flux:subheading size="sm">{{ __('Employee:') }} <strong>{{ $editingEmployeeName }}</strong></flux:subheading>
            </div>

            <form wire:submit="saveRates" class="flex flex-col gap-4">
                <div class="p-3 rounded-lg bg-zinc-50 dark:bg-zinc-800/80 border border-zinc-200 dark:border-zinc-700 flex items-center justify-between text-xs">
                    <span class="text-zinc-600 dark:text-zinc-400">{{ __('Standard Rates: ₹11/ft Med, ₹12/ft Heavy, ₹4/danda, ₹20/challi') }}</span>
                    <button type="button" wire:click="resetToDefaults" class="font-bold text-indigo-600 hover:text-indigo-700 dark:text-indigo-400">
                        {{ __('Use Standard') }}
                    </button>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" step="0.5" min="0" wire:model="ghodi_medium_rate" :label="__('Medium Ghodi Rate (₹/ft)')" placeholder="11.00" required />
                    <flux:input type="number" step="0.5" min="0" wire:model="ghodi_heavy_rate" :label="__('Heavy Ghodi Rate (₹/ft)')" placeholder="12.00" required />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <flux:input type="number" step="0.5" min="0" wire:model="siddi_danda_rate" :label="__('Siddi Rate (₹/danda)')" placeholder="4.00" required />
                    <flux:input type="number" step="0.5" min="0" wire:model="challi_rate" :label="__('Challi Rate (₹/pc)')" placeholder="20.00" required />
                </div>

                <flux:input type="number" step="10" min="0" wire:model="default_daily_rate" :label="__('Base Daily Rate (₹/day)')" placeholder="700.00" />

                <div class="flex justify-end gap-2 mt-2">
                    <flux:button type="button" variant="subtle" wire:click="$set('showEditModal', false)">{{ __('Cancel') }}</flux:button>
                    <flux:button type="submit" variant="primary">{{ __('Save Rates') }}</flux:button>
                </div>
            </form>
        </div>
    </flux:modal>
</div>
