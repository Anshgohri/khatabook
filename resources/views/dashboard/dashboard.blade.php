@component('layouts.app')
    <div class="flex flex-col gap-8">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
            <div>
                <flux:heading size="xl">{{ __('Dashboard & Financial Overview') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Real-time financial metrics, payouts to financiers, supplier outflows, and sales analytics.') }}</flux:text>
            </div>

            <div class="flex items-center gap-3">
                <form action="{{ route('dashboard.refresh') }}" method="POST">
                    @csrf
                    <flux:button variant="subtle" icon="arrow-path" type="submit">
                        {{ __('Refresh Stats') }}
                    </flux:button>
                </form>

                <flux:dropdown position="bottom" align="end">
                    <flux:button icon="bell" variant="ghost" data-test="notifications-button">
                        @if ($unreadNotificationsCount > 0)
                        <flux:badge color="red" size="sm">{{ $unreadNotificationsCount }}</flux:badge>
                        @endif
                    </flux:button>

                    <flux:menu class="w-80">
                        <div class="flex items-center justify-between px-3 py-2">
                            <flux:heading size="sm">{{ __('Notifications') }}</flux:heading>
                            @if ($unreadNotificationsCount > 0)
                            <form action="{{ route('dashboard.notifications.mark-read') }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="text-xs cursor-pointer text-blue-500 hover:underline">{{ __('Mark all read') }}</button>
                            </form>
                            @endif
                        </div>

                        <flux:menu.separator />

                        @forelse ($recentNotifications as $notification)
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
                    <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($salesToday, 2) }}</flux:heading>
                </flux:card>

                @if (auth()->user()?->isSystemAdmin())
                <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500 bg-teal-50/20 dark:bg-teal-950/10">
                    <flux:text size="sm" class="font-medium text-teal-800 dark:text-teal-300">{{ __('Today\'s Sales Profit') }}</flux:text>
                    <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($profitToday, 2) }}</flux:heading>
                </flux:card>
                @endif

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
                    <flux:text size="sm">{{ __('Sales This Month') }}</flux:text>
                    <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($salesMonth, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-emerald-500">
                    <flux:text size="sm">{{ __('Sales This Year') }}</flux:text>
                    <flux:heading size="lg" class="text-emerald-600 dark:text-emerald-400">₹{{ number_format($salesYear, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-rose-500">
                    <flux:text size="sm">{{ __('Expenses This Month') }}</flux:text>
                    <flux:heading size="lg" class="text-rose-600 dark:text-rose-400">₹{{ number_format($expensesMonth, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-indigo-500">
                    <flux:text size="sm">{{ __('Profit Margin') }}</flux:text>
                    <flux:heading size="lg" class="text-indigo-600 dark:text-indigo-400">{{ $profitMargin }}%</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-blue-500">
                    <flux:text size="sm">{{ __('Net Cash Flow (This Month)') }}</flux:text>
                    <flux:heading size="lg" class="{{ $cashFlow >= 0 ? 'text-blue-600 dark:text-blue-400' : 'text-red-600 dark:text-red-400' }}">
                        ₹{{ number_format($cashFlow, 2) }}
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
                    <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($financiersPaidToday, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-purple-500">
                    <flux:text size="sm">{{ __('Paid to Financiers (This Month)') }}</flux:text>
                    <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($financiersPaidMonth, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-purple-500">
                    <flux:text size="sm">{{ __('Paid to Financiers (This Year)') }}</flux:text>
                    <flux:heading size="lg" class="text-purple-600 dark:text-purple-400">₹{{ number_format($financiersPaidYear, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-amber-500 bg-amber-50/30 dark:bg-amber-950/10">
                    <flux:text size="sm">{{ __('Total Outstanding Financier Balance') }}</flux:text>
                    <flux:heading size="lg" class="text-amber-600 dark:text-amber-400">₹{{ number_format($financiersOutstanding, 2) }}</flux:heading>
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
                    <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($suppliersPaidToday, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500">
                    <flux:text size="sm">{{ __('Paid to Suppliers (This Month)') }}</flux:text>
                    <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($suppliersPaidMonth, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-orange-500">
                    <flux:text size="sm">{{ __('Paid to Suppliers (This Year)') }}</flux:text>
                    <flux:heading size="lg" class="text-orange-600 dark:text-orange-400">₹{{ number_format($suppliersPaidYear, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-red-500 bg-red-50/30 dark:bg-red-950/10">
                    <flux:text size="sm">{{ __('Total Balance Owed to Suppliers') }}</flux:text>
                    <flux:heading size="lg" class="text-red-600 dark:text-red-400">₹{{ number_format($suppliersOutstanding, 2) }}</flux:heading>
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
                    <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($employeesPaidToday, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500">
                    <flux:text size="sm">{{ __('Paid to Employees (This Month)') }}</flux:text>
                    <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($employeesPaidMonth, 2) }}</flux:heading>
                </flux:card>

                <flux:card class="flex flex-col gap-1 border-l-4 border-l-teal-500">
                    <flux:text size="sm">{{ __('Paid to Employees (This Year)') }}</flux:text>
                    <flux:heading size="lg" class="text-teal-600 dark:text-teal-400">₹{{ number_format($employeesPaidYear, 2) }}</flux:heading>
                </flux:card>
            </div>
        </div>

        {{-- ─── 5. Analytics & Charts ────────────────────────────────────────── --}}
        <div
            class="grid gap-6 lg:grid-cols-2 mt-2"
            x-data="khatabookCharts(@js($chartsPayload))">
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
@endcomponent