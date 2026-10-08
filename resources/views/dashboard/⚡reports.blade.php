<?php

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

new #[Title('Reports')] class extends Component {
    #[Url]
    public string $preset = 'month';

    public function mount(): void
    {
        $this->authorize('viewAny', Sale::class);
    }

    #[Computed]
    public function range(): array
    {
        return match ($this->preset) {
            'day' => [now()->startOfDay(), now()->endOfDay()],
            'year' => [now()->startOfYear(), now()->endOfYear()],
            default => [now()->startOfMonth(), now()->endOfMonth()],
        };
    }

    protected function scopedSales()
    {
        $user = Auth::user();
        [$from, $to] = $this->range;

        return Sale::query()
            ->when($user->isStaff(), fn ($query) => $query->where('user_id', $user->id))
            ->whereBetween('date', [$from, $to]);
    }

    protected function scopedExpenses()
    {
        $user = Auth::user();
        [$from, $to] = $this->range;

        return Expense::query()
            ->when($user->isStaff(), fn ($query) => $query->where('user_id', $user->id))
            ->whereBetween('date', [$from, $to]);
    }

    #[Computed]
    public function totalSales(): float
    {
        return (float) $this->scopedSales()->sum('total_amount');
    }

    #[Computed]
    public function totalSalesProfit(): float
    {
        $user = Auth::user();
        if (! $user?->isAdmin()) {
            return 0.0;
        }

        return (float) $this->scopedSales()
            ->with('items.product')
            ->get()
            ->sum(fn ($sale) => $sale->profit());
    }

    #[Computed]
    public function totalExpenses(): float
    {
        return (float) $this->scopedExpenses()->sum('amount');
    }

    #[Computed]
    public function salesByItem()
    {
        return $this->scopedSales()
            ->selectRaw('items_sold, sum(total_amount) as total, sum(quantity) as units')
            ->groupBy('items_sold')
            ->orderByDesc('total')
            ->get();
    }

    #[Computed]
    public function expensesByCategory()
    {
        return $this->scopedExpenses()
            ->with('category')
            ->selectRaw('expense_category_id, sum(amount) as total')
            ->groupBy('expense_category_id')
            ->orderByDesc('total')
            ->get();
    }

    public function exportSales()
    {
        $this->authorize('viewAny', Sale::class);

        $isAdmin = Auth::user()?->isAdmin();
        $sales = $this->scopedSales()->with(['user', 'items.product'])->orderBy('date')->get();

        return response()->streamDownload(function () use ($sales, $isAdmin) {
            $handle = fopen('php://output', 'w');
            $headers = ['Date', 'Customer', 'Items Sold', 'Quantity', 'Unit Price', 'Total Amount'];
            if ($isAdmin) {
                $headers[] = 'Profit';
            }
            $headers[] = 'Payment Status';
            $headers[] = 'Recorded By';

            fputcsv($handle, $headers);

            foreach ($sales as $sale) {
                $row = [
                    $sale->date->toDateString(),
                    $sale->customer_name,
                    $sale->items_sold,
                    $sale->quantity,
                    $sale->unit_price,
                    $sale->total_amount,
                ];
                if ($isAdmin) {
                    $row[] = number_format((float) $sale->profit(), 2, '.', '');
                }
                $row[] = $sale->payment_status;
                $row[] = $sale->user->name;

                fputcsv($handle, $row);
            }

            fclose($handle);
        }, 'sales-'.$this->preset.'-'.now()->format('Y-m-d').'.csv');
    }

    public function exportExpenses()
    {
        $this->authorize('viewAny', Expense::class);

        $expenses = $this->scopedExpenses()->with(['user', 'category'])->orderBy('date')->get();

        return response()->streamDownload(function () use ($expenses) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Category', 'Description', 'Amount', 'Payment Method', 'Recorded By']);

            foreach ($expenses as $expense) {
                fputcsv($handle, [
                    $expense->date->toDateString(),
                    $expense->category->name,
                    $expense->description,
                    $expense->amount,
                    $expense->payment_method,
                    $expense->user->name,
                ]);
            }

            fclose($handle);
        }, 'expenses-'.$this->preset.'-'.now()->format('Y-m-d').'.csv');
    }
}; ?>

<div class="flex flex-col gap-6">
    <div class="flex items-center justify-between">
        <flux:heading size="xl">{{ __('Reports') }}</flux:heading>

        <flux:select wire:model.live="preset" class="w-40">
            <flux:select.option value="day">{{ __('Daily') }}</flux:select.option>
            <flux:select.option value="month">{{ __('Monthly') }}</flux:select.option>
            <flux:select.option value="year">{{ __('Yearly') }}</flux:select.option>
        </flux:select>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 {{ auth()->user()?->isAdmin() ? 'lg:grid-cols-4' : 'lg:grid-cols-3' }}">
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
            <flux:text size="sm">{{ __('Total sales') }}</flux:text>
            <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->totalSales, 2) }}</flux:heading>
        </flux:card>

        @if (auth()->user()?->isAdmin())
        <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500 bg-teal-50/20 dark:bg-teal-950/10">
            <flux:text size="sm" class="font-medium text-teal-800 dark:text-teal-300">{{ __('Sales Gross Profit') }}</flux:text>
            <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($this->totalSalesProfit, 2) }}</flux:heading>
        </flux:card>
        @endif

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-rose-500">
            <flux:text size="sm">{{ __('Total expenses') }}</flux:text>
            <flux:heading size="lg" class="text-rose-600 dark:text-rose-400">₹{{ number_format($this->totalExpenses, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1 border-l-4 border-l-indigo-500">
            <flux:text size="sm">{{ __('Net profit (Sales - Expenses)') }}</flux:text>
            <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">₹{{ number_format($this->totalSales - $this->totalExpenses, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Sales by item') }}</flux:heading>
                <flux:button size="sm" icon="arrow-down-tray" wire:click="exportSales">{{ __('Export CSV') }}</flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Item') }}</flux:table.column>
                    <flux:table.column>{{ __('Units') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->salesByItem as $row)
                        <flux:table.row wire:key="sales-item-{{ $row->items_sold }}">
                            <flux:table.cell>{{ $row->items_sold }}</flux:table.cell>
                            <flux:table.cell>{{ $row->units }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $row->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="3" class="text-center text-zinc-500">{{ __('No sales in this period.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>

        <flux:card class="flex flex-col gap-4">
            <div class="flex items-center justify-between">
                <flux:heading size="lg">{{ __('Expenses by category') }}</flux:heading>
                <flux:button size="sm" icon="arrow-down-tray" wire:click="exportExpenses">{{ __('Export CSV') }}</flux:button>
            </div>

            <flux:table>
                <flux:table.columns>
                    <flux:table.column>{{ __('Category') }}</flux:table.column>
                    <flux:table.column>{{ __('Total') }}</flux:table.column>
                </flux:table.columns>
                <flux:table.rows>
                    @forelse ($this->expensesByCategory as $row)
                        <flux:table.row wire:key="expense-category-{{ $row->expense_category_id }}">
                            <flux:table.cell>{{ $row->category->name }}</flux:table.cell>
                            <flux:table.cell>{{ number_format((float) $row->total, 2) }}</flux:table.cell>
                        </flux:table.row>
                    @empty
                        <flux:table.row>
                            <flux:table.cell colspan="2" class="text-center text-zinc-500">{{ __('No expenses in this period.') }}</flux:table.cell>
                        </flux:table.row>
                    @endforelse
                </flux:table.rows>
            </flux:table>
        </flux:card>
    </div>
</div>
