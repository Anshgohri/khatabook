<?php

use App\Models\Expense;
use App\Models\Sale;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Dashboard')] class extends Component {
    protected function scopedSales()
    {
        $user = Auth::user();

        return Sale::query()->when($user->isStaff(), fn ($query) => $query->where('user_id', $user->id));
    }

    protected function scopedExpenses()
    {
        $user = Auth::user();

        return Expense::query()->when($user->isStaff(), fn ($query) => $query->where('user_id', $user->id));
    }

    #[Computed]
    public function salesToday(): float
    {
        return (float) $this->scopedSales()->whereDate('date', today())->sum('total_amount');
    }

    #[Computed]
    public function salesMonth(): float
    {
        return (float) $this->scopedSales()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount');
    }

    #[Computed]
    public function salesYear(): float
    {
        return (float) $this->scopedSales()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('total_amount');
    }

    #[Computed]
    public function expensesMonth(): float
    {
        return (float) $this->scopedExpenses()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');
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
        unset($this->salesToday, $this->salesMonth, $this->salesYear, $this->expensesMonth, $this->profitMargin, $this->cashFlow, $this->unreadNotificationsCount, $this->recentNotifications);

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
        $days = collect(range(13, 0))->map(fn ($i) => now()->subDays($i)->toDateString());

        $totals = $this->scopedSales()
            ->whereDate('date', '>=', now()->subDays(13)->toDateString())
            ->selectRaw('date, sum(total_amount) as total')
            ->groupBy('date')
            ->pluck('total', 'date');

        return [
            'labels' => $days->map(fn ($day) => Carbon::parse($day)->format('d M'))->all(),
            'values' => $days->map(fn ($day) => (float) ($totals[$day] ?? 0))->all(),
        ];
    }

    protected function expenseBreakdownData(): array
    {
        $rows = $this->scopedExpenses()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->with('category')
            ->selectRaw('expense_category_id, sum(amount) as total')
            ->groupBy('expense_category_id')
            ->get();

        return [
            'labels' => $rows->map(fn ($row) => $row->category->name)->all(),
            'values' => $rows->map(fn ($row) => (float) $row->total)->all(),
        ];
    }

    protected function topItemsData(): array
    {
        $rows = $this->scopedSales()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->selectRaw('items_sold, sum(total_amount) as total')
            ->groupBy('items_sold')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        return [
            'labels' => $rows->pluck('items_sold')->all(),
            'values' => $rows->pluck('total')->map(fn ($value) => (float) $value)->all(),
        ];
    }
}; ?>

<div class="flex flex-col gap-6" wire:poll.15s="refreshDashboard">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <flux:heading size="xl">{{ __('Dashboard') }}</flux:heading>

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

    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Sales today') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->salesToday, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Sales this month') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->salesMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Sales this year') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->salesYear, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Expenses this month') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->expensesMonth, 2) }}</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Profit margin') }}</flux:text>
            <flux:heading size="lg">{{ $this->profitMargin }}%</flux:heading>
        </flux:card>

        <flux:card class="flex flex-col gap-1">
            <flux:text size="sm">{{ __('Cash flow') }}</flux:text>
            <flux:heading size="lg">{{ number_format($this->cashFlow, 2) }}</flux:heading>
        </flux:card>
    </div>

    <div
        class="grid gap-6 lg:grid-cols-2"
        wire:ignore
        x-data="khatabookCharts(@js($this->chartsPayload()))"
        x-on:dashboard-refreshed.window="update($event.detail)"
    >
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
