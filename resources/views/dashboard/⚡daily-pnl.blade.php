<?php

use App\Models\Sale;
use App\Models\Expense;
use App\Models\FinancierPayment;
use App\Models\SupplierPayment;
use App\Models\EmployeePayment;
use App\Models\ProductionLog;
use Carbon\Carbon;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Daily P&L')] class extends Component {
    use WithPagination;

    public function mount(): void
    {
        $this->authorize('viewAny', Sale::class);
    }

    #[Computed]
    public function totalSales(): float
    {
        return (float) Sale::sum('total_amount');
    }

    #[Computed]
    public function totalOutflow(): float
    {
        return (float) Expense::sum('amount') 
             + (float) FinancierPayment::where('type', 'installment_paid')->sum('amount') 
             + (float) SupplierPayment::where('type', 'payment_made')->sum('amount') 
             + (float) EmployeePayment::sum('amount');
    }

    #[Computed]
    public function netPosition(): float
    {
        return $this->totalSales - $this->totalOutflow;
    }

    #[Computed]
    public function dailyRecords()
    {
        $dates = collect();

        $dates = $dates->merge(Sale::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));
        $dates = $dates->merge(Expense::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));
        $dates = $dates->merge(FinancierPayment::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));
        $dates = $dates->merge(SupplierPayment::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));
        $dates = $dates->merge(EmployeePayment::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));
        $dates = $dates->merge(ProductionLog::pluck('date')->map(fn($d) => Carbon::parse($d)->format('Y-m-d')));

        $uniqueDates = $dates->unique()->sortDesc()->values();
        
        $page = $this->getPage();
        $perPage = 15;
        $paginatedDates = new \Illuminate\Pagination\LengthAwarePaginator(
            $uniqueDates->slice(($page - 1) * $perPage, $perPage),
            $uniqueDates->count(),
            $perPage,
            $page,
            ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
        );

        $records = [];
        foreach ($paginatedDates as $date) {
            $sales = (float) Sale::whereDate('date', $date)->sum('total_amount');
            $expenses = (float) Expense::whereDate('date', $date)->sum('amount');
            $financier = (float) FinancierPayment::whereDate('date', $date)->where('type', 'installment_paid')->sum('amount');
            $supplier = (float) SupplierPayment::whereDate('date', $date)->where('type', 'payment_made')->sum('amount');
            $employee = (float) EmployeePayment::whereDate('date', $date)->sum('amount');
            
            $productionLogs = ProductionLog::with(['rawMaterial', 'items.rawMaterial'])->whereDate('date', $date)->get();
            $productionCost = 0;
            foreach ($productionLogs as $log) {
                $cost = (float) $log->worker_wage;
                if ($log->items->count() > 0) {
                    foreach ($log->items as $item) {
                        if ($item->rawMaterial) {
                            $cost += $item->raw_material_consumed_qty * (float) $item->rawMaterial->cost_price;
                        }
                    }
                } else if ($log->rawMaterial) {
                    $cost += $log->raw_material_consumed_qty * (float) $log->rawMaterial->cost_price;
                }
                $productionCost += $cost;
            }

            $totalOutflow = $expenses + $financier + $supplier + $employee;
            $net = $sales - $totalOutflow;

            $records[] = [
                'date' => $date,
                'sales' => $sales,
                'expenses' => $expenses,
                'financier' => $financier,
                'supplier' => $supplier,
                'employee' => $employee,
                'production' => $productionCost,
                'outflow' => $totalOutflow,
                'net' => $net,
            ];
        }

        return [
            'paginator' => $paginatedDates,
            'records' => $records,
        ];
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Daily Profit & Loss (Cash Flow)') }}</flux:heading>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10">
            <flux:text size="sm" class="font-medium text-emerald-800 dark:text-emerald-300">{{ __('Total All-Time Sales') }}</flux:text>
            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->totalSales, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-rose-500 bg-rose-50/20 dark:bg-rose-950/10">
            <flux:text size="sm" class="font-medium text-rose-800 dark:text-rose-300">{{ __('Total All-Time Outflow') }}</flux:text>
            <flux:heading size="lg" class="text-rose-600 dark:text-rose-400">₹{{ number_format($this->totalOutflow, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 {{ $this->netPosition >= 0 ? 'border-l-emerald-500 bg-emerald-50/20 dark:bg-emerald-950/10' : 'border-l-red-500 bg-red-50/20 dark:bg-red-950/10' }}">
            <flux:text size="sm" class="font-medium {{ $this->netPosition >= 0 ? 'text-emerald-800 dark:text-emerald-300' : 'text-red-800 dark:text-red-300' }}">{{ __('Current Net Position') }}</flux:text>
            <flux:heading size="lg" class="{{ $this->netPosition >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ $this->netPosition >= 0 ? '+' : '' }}₹{{ number_format($this->netPosition, 2) }}
            </flux:heading>
        </flux:card>
    </div>

    <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
        <flux:table :paginate="$this->dailyRecords['paginator']">
            <flux:table.columns>
                <flux:table.column>{{ __('Date') }}</flux:table.column>
                <flux:table.column>{{ __('Sales (In)') }}</flux:table.column>
                <flux:table.column>{{ __('Expenses (Out)') }}</flux:table.column>
                <flux:table.column>{{ __('Financier Paid') }}</flux:table.column>
                <flux:table.column>{{ __('Supplier Paid') }}</flux:table.column>
                <flux:table.column>{{ __('Employee Paid') }}</flux:table.column>
                <flux:table.column>{{ __('Total Outflow') }}</flux:table.column>
                <flux:table.column>{{ __('Net Cash P&L') }}</flux:table.column>
                <flux:table.column>{{ __('Production Log') }}</flux:table.column>
            </flux:table.columns>
            
            <flux:table.rows>
                @forelse ($this->dailyRecords['records'] as $record)
                <flux:table.row>
                    <flux:table.cell class="font-medium whitespace-nowrap">{{ \Carbon\Carbon::parse($record['date'])->format('d M Y') }}</flux:table.cell>
                    <flux:table.cell class="text-emerald-600 dark:text-emerald-400 font-semibold">+₹{{ number_format($record['sales'], 2) }}</flux:table.cell>
                    <flux:table.cell class="text-rose-600 dark:text-rose-400">-₹{{ number_format($record['expenses'], 2) }}</flux:table.cell>
                    <flux:table.cell class="text-rose-600 dark:text-rose-400">-₹{{ number_format($record['financier'], 2) }}</flux:table.cell>
                    <flux:table.cell class="text-rose-600 dark:text-rose-400">-₹{{ number_format($record['supplier'], 2) }}</flux:table.cell>
                    <flux:table.cell class="text-rose-600 dark:text-rose-400">-₹{{ number_format($record['employee'], 2) }}</flux:table.cell>
                    <flux:table.cell class="font-bold text-rose-600 dark:text-rose-400">-₹{{ number_format($record['outflow'], 2) }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge :color="$record['net'] >= 0 ? 'green' : 'red'">
                            {{ $record['net'] >= 0 ? '+' : '' }}₹{{ number_format($record['net'], 2) }}
                        </flux:badge>
                    </flux:table.cell>
                    <flux:table.cell class="text-amber-600 dark:text-amber-400" title="{{ __('Cost of raw materials and worker wages for items produced this day') }}">
                        ₹{{ number_format($record['production'], 2) }}
                    </flux:table.cell>
                </flux:table.row>
                @empty
                <flux:table.row>
                    <flux:table.cell colspan="9" class="text-center text-zinc-500">{{ __('No daily records found.') }}</flux:table.cell>
                </flux:table.row>
                @endforelse
            </flux:table.rows>
        </flux:table>
    </div>
</div>
