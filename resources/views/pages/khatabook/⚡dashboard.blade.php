<?php

use App\Models\EmployeePayment;
use App\Models\Expense;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\SupplierPayment;
use App\Services\CacheService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    public function mount()
    {
        $user = Auth::user();
        if ($user->isFinancier()) {
            return $this->redirect(route('financiers'), navigate: true);
        }
        if ($user->isCustomer()) {
            return $this->redirect(route('my-orders'), navigate: true);
        }
    }

    protected function scopedSales()
    {
        $user = Auth::user();

        return Sale::query()->when($user->isStaff(), fn($query) => $query->where('user_id', $user->id));
    }

    protected function scopedExpenses()
    {
        $user = Auth::user();

        return Expense::query()->when(! $user->isSystemAdmin(), fn($query) => $query->where('user_id', $user->id));
    }

    protected function scopedFinancierPayments()
    {
        $user = Auth::user();

        return FinancierPayment::query()
            ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isFinancier(), fn ($query) => $query->whereHas('financier', fn ($q) => $q->where('financier_user_id', $user->id)))
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment']);
    }

    protected function scopedSupplierPayments()
    {
        $user = Auth::user();

        return SupplierPayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->where('type', 'payment_made');
    }

    protected function scopedEmployeePayments()
    {
        $user = Auth::user();

        return EmployeePayment::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id));
    }

    #[Computed]
    public function salesToday(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.sales_today', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSales()->whereDate('date', today())->sum('total_amount');
        });
    }

    #[Computed]
    public function profitToday(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.profit_today', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSales()
                ->whereDate('date', today())
                ->with('items.product')
                ->get()
                ->sum(fn ($sale) => $sale->profit());
        });
    }

    #[Computed]
    public function profitMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.profit_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSales()
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->with('items.product')
                ->get()
                ->sum(fn ($sale) => $sale->profit());
        });
    }

    #[Computed]
    public function salesMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.sales_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSales()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount');
        });
    }

    #[Computed]
    public function salesYear(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.sales_year', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSales()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('total_amount');
        });
    }

    #[Computed]
    public function expensesMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.expenses_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedExpenses()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        });
    }

    #[Computed]
    public function profitMargin(): float
    {
        return $this->salesMonth > 0
            ? round((($this->salesMonth - $this->expensesMonth) / $this->salesMonth) * 100, 1)
            : 0.0;
    }

    #[Computed]
    public function cashFlow(): float
    {
        return $this->salesMonth - $this->expensesMonth;
    }

    #[Computed]
    public function financiersPaidToday(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.financiers_today', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedFinancierPayments()->whereDate('date', today())->sum('amount');
        });
    }

    #[Computed]
    public function financiersPaidMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.financiers_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedFinancierPayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        });
    }

    #[Computed]
    public function financiersPaidYear(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.financiers_year', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedFinancierPayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');
        });
    }

    #[Computed]
    public function financiersOutstanding(): float
    {
        $userId = Auth::id();
        $user = Auth::user();

        return CacheService::rememberForUser('dashboard.financiers_outstanding', $userId, CacheService::STATS_TTL, function () use ($user) {
            return (float) Financier::query()
                ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
                ->when($user->isFinancier(), fn ($query) => $query->where('financier_user_id', $user->id))
                ->sum('outstanding_balance');
        });
    }

    #[Computed]
    public function suppliersPaidToday(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.suppliers_today', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSupplierPayments()->whereDate('date', today())->sum('amount');
        });
    }

    #[Computed]
    public function suppliersPaidMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.suppliers_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSupplierPayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        });
    }

    #[Computed]
    public function suppliersPaidYear(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.suppliers_year', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedSupplierPayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');
        });
    }

    #[Computed]
    public function suppliersOutstanding(): float
    {
        $userId = Auth::id();
        $user = Auth::user();

        return CacheService::rememberForUser('dashboard.suppliers_outstanding', $userId, CacheService::STATS_TTL, function () use ($user) {
            return (float) Supplier::query()
                ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
                ->sum('outstanding_balance');
        });
    }

    #[Computed]
    public function employeesPaidToday(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.employees_today', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedEmployeePayments()->whereDate('date', today())->sum('amount');
        });
    }

    #[Computed]
    public function employeesPaidMonth(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.employees_month', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedEmployeePayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
        });
    }

    #[Computed]
    public function employeesPaidYear(): float
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.employees_year', $userId, CacheService::STATS_TTL, function () {
            return (float) $this->scopedEmployeePayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');
        });
    }

    #[Computed]
    public function unreadNotificationsCount(): int
    {
        return Auth::user()->unreadNotifications()->count();
    }

    #[Computed]
    public function recentNotifications()
    {
        return Auth::user()->notifications()->latest()->limit(8)->get();
    }

    public function markAllNotificationsRead(): void
    {
        Auth::user()->unreadNotifications->markAsRead();

        unset($this->unreadNotificationsCount, $this->recentNotifications);
    }

    public function refreshDashboard(): void
    {
        // Bust cache before unsetting computed properties
        CacheService::invalidateUser(Auth::id());

        unset(
            $this->salesToday,
            $this->profitToday,
            $this->salesMonth,
            $this->profitMonth,
            $this->salesYear,
            $this->expensesMonth,
            $this->profitMargin,
            $this->cashFlow,
            $this->financiersPaidToday,
            $this->financiersPaidMonth,
            $this->financiersPaidYear,
            $this->financiersOutstanding,
            $this->suppliersPaidToday,
            $this->suppliersPaidMonth,
            $this->suppliersPaidYear,
            $this->suppliersOutstanding,
            $this->employeesPaidToday,
            $this->employeesPaidMonth,
            $this->employeesPaidYear,
            $this->unreadNotificationsCount,
            $this->recentNotifications
        );

        $this->dispatch('dashboard-refreshed', ...$this->chartsPayload());
    }

    protected function chartsPayload(): array
    {
        return [
            'salesTrend' => $this->salesTrendData(),
            'expenseBreakdown' => $this->expenseBreakdownData(),
            'topItems' => $this->topItemsData(),
        ];
    }

    protected function salesTrendData(): array
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.sales_trend', $userId, CacheService::STATS_TTL, function () {
            $days = collect(range(13, 0))->map(fn($i) => now()->subDays($i)->toDateString());

            $totals = $this->scopedSales()
                ->whereDate('date', '>=', now()->subDays(13)->toDateString())
                ->selectRaw('date, sum(total_amount) as total')
                ->groupBy('date')
                ->pluck('total', 'date');

            return [
                'labels' => $days->map(fn($day) => Carbon::parse($day)->format('d M'))->all(),
                'values' => $days->map(fn($day) => (float) ($totals[$day] ?? 0))->all(),
            ];
        });
    }

    protected function expenseBreakdownData(): array
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.expense_breakdown', $userId, CacheService::STATS_TTL, function () {
            $rows = $this->scopedExpenses()
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->with('category')
                ->selectRaw('expense_category_id, sum(amount) as total')
                ->groupBy('expense_category_id')
                ->get();

            return [
                'labels' => $rows->map(fn($row) => $row->category->name)->all(),
                'values' => $rows->map(fn($row) => (float) $row->total)->all(),
            ];
        });
    }

    protected function topItemsData(): array
    {
        $userId = Auth::id();

        return CacheService::rememberForUser('dashboard.top_items', $userId, CacheService::STATS_TTL, function () {
            $rows = $this->scopedSales()
                ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
                ->selectRaw('items_sold, sum(total_amount) as total')
                ->groupBy('items_sold')
                ->orderByDesc('total')
                ->limit(6)
                ->get();

            return [
                'labels' => $rows->pluck('items_sold')->all(),
                'values' => $rows->pluck('total')->map(fn($value) => (float) $value)->all(),
            ];
        });
    }
}; ?>


<div class="flex flex-col gap-8" wire:poll.60s="refreshDashboard">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <flux:heading size="xl">{{ __('Dashboard & Financial Overview') }}</flux:heading>
            <flux:text class="mt-1">{{ __('Real-time financial metrics, payouts to financiers, supplier outflows, and sales analytics.') }}</flux:text>
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="subtle" icon="arrow-path" wire:click="refreshDashboard">
                {{ __('Refresh Stats') }}
            </flux:button>

            <flux:dropdown position="bottom" align="end">
                <flux:button icon="bell" variant="ghost" data-test="notifications-button">
                    @if ($this->unreadNotificationsCount > 0)
                    <flux:badge color="red" size="sm">{{ $this->unreadNotificationsCount }}</flux:badge>
                    @endif
                </flux:button>

                <flux:menu class="w-80">
                    <div class="flex items-center justify-between px-3 py-2">
                        <flux:heading size="sm">{{ __('Notifications') }}</flux:heading>
                        @if ($this->unreadNotificationsCount > 0)
                        <flux:link class="text-xs cursor-pointer" wire:click.prevent="markAllNotificationsRead">{{ __('Mark all read') }}</flux:link>
                        @endif
                    </div>

                    <flux:menu.separator />

                    @forelse ($this->recentNotifications as $notification)
                    <div class="px-3 py-2 text-sm {{ $notification->read_at ? 'text-zinc-400' : 'text-zinc-800 dark:text-white' }}">
                        {{ $notification->data['message'] ?? '' }}
                    </div>
                    @empty
                    <div class="px-3 py-2 text-sm text-zinc-500">{{ __('No notifications yet.') }}</div>
                    @endforelse
                </flux:menu>
            </flux:dropdown>
        </div>
    </div>

    {{-- ─── 1. Primary Revenue & Profit Overview ────────────────────────────── --}}
    <div class="flex flex-col gap-3">
        <flux:heading size="lg" class="flex items-center gap-2">
            <flux:icon name="banknotes" class="w-5 h-5 text-emerald-500" />
            {{ __('Sales & Cash Flow Summary') }}
        </flux:heading>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
                <flux:text size="sm">{{ __('Sales Today') }}</flux:text>
                <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->salesToday, 2) }}</flux:heading>
            </flux:card>

            @if (auth()->user()?->isAdmin())
            <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500 bg-teal-50/20 dark:bg-teal-950/10">
                <flux:text size="sm" class="font-medium text-teal-800 dark:text-teal-300">{{ __('Today\'s Sales Profit') }}</flux:text>
                <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($this->profitToday, 2) }}</flux:heading>
            </flux:card>
            @endif

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
                <flux:text size="sm">{{ __('Sales This Month') }}</flux:text>
                <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->salesMonth, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
                <flux:text size="sm">{{ __('Sales This Year') }}</flux:text>
                <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($this->salesYear, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-rose-500">
                <flux:text size="sm">{{ __('Expenses This Month') }}</flux:text>
                <flux:heading size="lg" class="text-rose-600 dark:text-rose-400">₹{{ number_format($this->expensesMonth, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-indigo-500">
                <flux:text size="sm">{{ __('Profit Margin') }}</flux:text>
                <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">{{ $this->profitMargin }}%</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-blue-500">
                <flux:text size="sm">{{ __('Net Cash Flow (This Month)') }}</flux:text>
                <flux:heading size="lg" class="{{ $this->cashFlow >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                    ₹{{ number_format($this->cashFlow, 2) }}
                </flux:heading>
            </flux:card>
        </div>
    </div>

    {{-- ─── 2. Financier Payouts (Requested Section) ───────────────────────── --}}
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon name="currency-rupee" class="w-5 h-5 text-purple-500" />
                {{ __('Financier Payouts & Loan Repayments') }}
            </flux:heading>
            <flux:button size="sm" variant="subtle" icon="arrow-right" :href="route('financiers')" wire:navigate>
                {{ __('Manage Financiers') }}
            </flux:button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="flex flex-col gap-1 border-l-4 border-l-purple-500">
                <flux:text size="sm">{{ __('Paid to Financiers Today') }}</flux:text>
                <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($this->financiersPaidToday, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-purple-500">
                <flux:text size="sm">{{ __('Paid to Financiers (This Month)') }}</flux:text>
                <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($this->financiersPaidMonth, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-purple-500">
                <flux:text size="sm">{{ __('Paid to Financiers (This Year)') }}</flux:text>
                <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($this->financiersPaidYear, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-amber-500 bg-amber-50/30 dark:bg-amber-950/10">
                <flux:text size="sm">{{ __('Total Outstanding Financier Balance') }}</flux:text>
                <flux:heading size="lg" class="text-amber-600 dark:text-amber-400">₹{{ number_format($this->financiersOutstanding, 2) }}</flux:heading>
            </flux:card>
        </div>
    </div>

    {{-- ─── 3. Supplier Payments & Raw Material Outflows ────────────────────── --}}
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon name="truck" class="w-5 h-5 text-orange-500" />
                {{ __('Supplier & Raw Material Payments') }}
            </flux:heading>
            <flux:button size="sm" variant="subtle" icon="arrow-right" :href="route('suppliers')" wire:navigate>
                {{ __('Manage Suppliers') }}
            </flux:button>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500">
                <flux:text size="sm">{{ __('Paid to Suppliers Today') }}</flux:text>
                <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->suppliersPaidToday, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500">
                <flux:text size="sm">{{ __('Paid to Suppliers (This Month)') }}</flux:text>
                <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->suppliersPaidMonth, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500">
                <flux:text size="sm">{{ __('Paid to Suppliers (This Year)') }}</flux:text>
                <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($this->suppliersPaidYear, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-red-500 bg-red-50/30 dark:bg-red-950/10">
                <flux:text size="sm">{{ __('Total Balance Owed to Suppliers') }}</flux:text>
                <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format($this->suppliersOutstanding, 2) }}</flux:heading>
            </flux:card>
        </div>
    </div>

    {{-- ─── 4. Employee Payroll & Wages ───────────────────────────────────── --}}
    <div class="flex flex-col gap-3">
        <div class="flex items-center justify-between">
            <flux:heading size="lg" class="flex items-center gap-2">
                <flux:icon name="users" class="w-5 h-5 text-teal-500" />
                {{ __('Employee Payroll & Wage Payments') }}
            </flux:heading>
            <flux:button size="sm" variant="subtle" icon="arrow-right" :href="route('employees')" wire:navigate>
                {{ __('Manage Employees') }}
            </flux:button>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500">
                <flux:text size="sm">{{ __('Paid to Employees Today') }}</flux:text>
                <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($this->employeesPaidToday, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500">
                <flux:text size="sm">{{ __('Paid to Employees (This Month)') }}</flux:text>
                <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($this->employeesPaidMonth, 2) }}</flux:heading>
            </flux:card>

            <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500">
                <flux:text size="sm">{{ __('Paid to Employees (This Year)') }}</flux:text>
                <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($this->employeesPaidYear, 2) }}</flux:heading>
            </flux:card>
        </div>
    </div>

    {{-- ─── 5. Analytics & Charts ────────────────────────────────────────── --}}
    <div
        class="grid gap-6 lg:grid-cols-2 mt-2"
        wire:ignore
        x-data="khatabookCharts(@js($this->chartsPayload()))"
        x-on:dashboard-refreshed.window="update($event.detail)">
        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Sales trend (last 14 days)') }}</flux:heading>
            <div class="h-64"><canvas x-ref="salesTrend"></canvas></div>
        </flux:card>

        <flux:card>
            <flux:heading size="lg" class="mb-4">{{ __('Expense breakdown (this month)') }}</flux:heading>
            <div class="h-64"><canvas x-ref="expenseBreakdown"></canvas></div>
        </flux:card>

        <flux:card class="lg:col-span-2">
            <flux:heading size="lg" class="mb-4">{{ __('Top items by revenue (this month)') }}</flux:heading>
            <div class="h-64"><canvas x-ref="topItems"></canvas></div>
        </flux:card>
    </div>

    @vite('resources/js/khatabook-charts.js')
</div>