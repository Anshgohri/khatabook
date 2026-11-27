@props(['cashSplit'])

<div x-data="{ showFinanciers: false }" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 rounded-2xl p-5 shadow-xs flex flex-col gap-5">
    {{-- Top Header Row --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <div class="p-2.5 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 text-white shadow-sm">
                <flux:icon name="banknotes" class="size-6" />
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <flux:heading size="lg" class="font-bold text-zinc-900 dark:text-zinc-100">
                        {{ __('Daily Cash Split & Financer Pot') }}
                    </flux:heading>
                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-300">
                        ● {{ __('Live Real-Time') }}
                    </span>
                </div>
                <flux:text size="sm" class="text-zinc-500 dark:text-zinc-400 mt-0.5 flex flex-wrap items-center gap-2">
                    <span>{{ __('Auto-split from today\'s sales:') }} <span class="font-bold text-zinc-800 dark:text-zinc-200">₹{{ number_format($cashSplit['sales_today'] ?? 0, 2) }}</span> ({{ $cashSplit['date_human'] ?? now()->format('d M Y') }})</span>
                    <span class="text-zinc-300 dark:text-zinc-700 hidden sm:inline">•</span>
                    <span>{{ __('Outflows:') }} <strong class="text-zinc-800 dark:text-zinc-200">₹{{ number_format($cashSplit['total_cash_out_today'] ?? 0, 2) }}</strong></span>
                    <span class="text-zinc-300 dark:text-zinc-700 hidden sm:inline">•</span>
                    <span class="font-semibold {{ ($cashSplit['net_cash_flow_today'] ?? 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                        {{ ($cashSplit['net_cash_flow_today'] ?? 0) >= 0 ? __('Net Cash: +₹') . number_format($cashSplit['net_cash_flow_today'] ?? 0, 2) : __('Net Deficit: -₹') . number_format(abs($cashSplit['net_cash_flow_today'] ?? 0), 2) }}
                    </span>
                </flux:text>
            </div>
        </div>

        <div class="flex items-center gap-2">
            <flux:button 
                variant="subtle" 
                size="sm" 
                icon="chevron-down" 
                x-on:click="showFinanciers = !showFinanciers"
            >
                <span x-text="showFinanciers ? '{{ __('Hide Financer Due Dates') }}' : '{{ __('View 17 Financers & Due Dates') }}'"></span>
                <flux:badge size="xs" color="purple" class="ml-1.5">{{ $cashSplit['financier_count'] ?? 0 }}</flux:badge>
            </flux:button>
            <flux:button size="sm" variant="ghost" :href="route('financiers')" wire:navigate icon="arrow-top-right-on-square">
                {{ __('Financiers Page') }}
            </flux:button>
        </div>
    </div>

    {{-- The Three Core Numbers (72% / 21% / 7%) --}}
    <div class="grid gap-4 sm:grid-cols-3">
        {{-- 1. Material Reserve (72%) --}}
        <div class="flex flex-col gap-2 p-4 rounded-xl border-l-4 border-l-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 border border-emerald-100 dark:border-emerald-900/50">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-emerald-800 dark:text-emerald-300">
                    {{ __('Material Reserve') }}
                </span>
                <span class="px-2 py-0.5 text-xs font-black rounded-md bg-emerald-500 text-white shadow-xs">
                    72%
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-emerald-900 dark:text-emerald-100">
                ₹{{ number_format($cashSplit['material_reserve'] ?? 0, 2) }}
            </div>
            <div class="text-xs text-emerald-700 dark:text-emerald-400 flex items-center justify-between">
                <span class="flex items-center gap-1">
                    <flux:icon name="cube" class="size-3.5 inline shrink-0" />
                    <span>{{ __('Spent today:') }} ₹{{ number_format($cashSplit['material_spent_today'] ?? 0, 2) }}</span>
                </span>
                <span class="font-medium {{ ($cashSplit['material_remaining'] ?? 0) >= 0 ? 'text-emerald-700 dark:text-emerald-300' : 'text-red-600 dark:text-red-400 font-bold' }}">
                    {{ ($cashSplit['material_remaining'] ?? 0) >= 0 ? __('Reserve Left:') . ' ₹' . number_format($cashSplit['material_remaining'] ?? 0, 2) : __('Deficit:') . ' -₹' . number_format(abs($cashSplit['material_remaining'] ?? 0), 2) }}
                </span>
            </div>
        </div>

        {{-- 2. Financer Pot (21%) --}}
        <div class="flex flex-col gap-2 p-4 rounded-xl border-l-4 border-l-purple-500 bg-purple-50/40 dark:bg-purple-950/20 border border-purple-100 dark:border-purple-900/50">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-purple-800 dark:text-purple-300">
                    {{ __('Financer Pot') }}
                </span>
                <span class="px-2 py-0.5 text-xs font-black rounded-md bg-purple-500 text-white shadow-xs">
                    21%
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-purple-900 dark:text-purple-100">
                ₹{{ number_format($cashSplit['financer_pot'] ?? 0, 2) }}
            </div>
            <div class="text-xs text-purple-700 dark:text-purple-400 flex items-center justify-between">
                <span class="flex items-center gap-1">
                    <flux:icon name="currency-rupee" class="size-3.5 inline shrink-0" />
                    <span>{{ __('Paid today:') }} ₹{{ number_format($cashSplit['financiers_paid_today'] ?? 0, 2) }}</span>
                </span>
                <span class="font-medium {{ ($cashSplit['pot_remaining'] ?? 0) >= 0 ? 'text-purple-600 dark:text-purple-300' : 'text-red-600 dark:text-red-400 font-bold' }}">
                    {{ ($cashSplit['pot_remaining'] ?? 0) >= 0 ? __('Pot Surplus:') . ' ₹' . number_format($cashSplit['pot_remaining'] ?? 0, 2) : __('Deficit:') . ' -₹' . number_format(abs($cashSplit['pot_remaining'] ?? 0), 2) }}
                </span>
            </div>
        </div>

        {{-- 3. Home Draw (7%) --}}
        <div class="flex flex-col gap-2 p-4 rounded-xl border-l-4 border-l-amber-500 bg-amber-50/40 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/50">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold uppercase tracking-wider text-amber-800 dark:text-amber-300">
                    {{ __('Home Draw') }}
                </span>
                <span class="px-2 py-0.5 text-xs font-black rounded-md bg-amber-500 text-white shadow-xs">
                    7%
                </span>
            </div>
            <div class="text-2xl sm:text-3xl font-black text-amber-900 dark:text-amber-100">
                ₹{{ number_format($cashSplit['home_draw'] ?? 0, 2) }}
            </div>
            <div class="text-xs text-amber-700 dark:text-amber-400 flex items-center justify-between">
                <span class="flex items-center gap-1">
                    <flux:icon name="home" class="size-3.5 inline shrink-0" />
                    <span>{{ __('Drawn today:') }} ₹{{ number_format($cashSplit['home_draw_spent_today'] ?? 0, 2) }}</span>
                </span>
                <span class="font-medium {{ ($cashSplit['home_draw_remaining'] ?? 0) >= 0 ? 'text-amber-700 dark:text-amber-300' : 'text-red-600 dark:text-red-400 font-bold' }}">
                    {{ ($cashSplit['home_draw_remaining'] ?? 0) >= 0 ? __('Safe Left:') . ' ₹' . number_format($cashSplit['home_draw_remaining'] ?? 0, 2) : __('Deficit:') . ' -₹' . number_format(abs($cashSplit['home_draw_remaining'] ?? 0), 2) }}
                </span>
            </div>
        </div>
    </div>

    {{-- Financier Due Dates & Running Progress Summary --}}
    <div class="flex flex-col gap-3 pt-2 border-t border-zinc-100 dark:border-zinc-800">
        <div class="flex flex-wrap items-center justify-between gap-2 text-xs">
            <div class="flex flex-wrap items-center gap-3">
                <span class="font-semibold text-zinc-700 dark:text-zinc-300 flex items-center gap-1">
                    <flux:icon name="clock" class="size-4 text-purple-500" />
                    {{ __('Financer Due Running Totals:') }}
                </span>
                <span class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Daily Dues:') }} <strong class="text-zinc-900 dark:text-zinc-100">₹{{ number_format($cashSplit['total_daily_installments'] ?? 0, 2) }}</strong>
                </span>
                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                <span class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Weekly Target:') }} <strong class="text-zinc-900 dark:text-zinc-100">₹{{ number_format($cashSplit['total_weekly_installments'] ?? 0, 2) }}</strong>
                </span>
                <span class="text-zinc-300 dark:text-zinc-700">•</span>
                <span class="text-zinc-600 dark:text-zinc-400">
                    {{ __('Monthly Target:') }} <strong class="text-zinc-900 dark:text-zinc-100">₹{{ number_format($cashSplit['total_monthly_installments'] ?? 0, 2) }}</strong>
                </span>
            </div>
            <button 
                type="button" 
                x-on:click="showFinanciers = !showFinanciers"
                class="text-indigo-600 dark:text-indigo-400 hover:underline font-semibold flex items-center gap-1 cursor-pointer"
            >
                <span x-text="showFinanciers ? '{{ __('Collapse Financer List ▲') }}' : '{{ __('Show All Financers Due Dates ▼') }}'"></span>
            </button>
        </div>

        {{-- Expandable Financers Table --}}
        <div x-show="showFinanciers" x-cloak class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700 mt-2">
            <table class="w-full text-xs text-left">
                <thead class="bg-zinc-50 dark:bg-zinc-800/60 text-zinc-500 font-semibold border-b border-zinc-200 dark:border-zinc-700 uppercase">
                    <tr>
                        <th class="px-3 py-2.5">{{ __('Financier') }}</th>
                        <th class="px-3 py-2.5">{{ __('Payout Cycle') }}</th>
                        <th class="px-3 py-2.5 text-right">{{ __('Due / Rate') }}</th>
                        <th class="px-3 py-2.5 text-right">{{ __('Paid Today') }}</th>
                        <th class="px-3 py-2.5 text-right">{{ __('Outstanding') }}</th>
                        <th class="px-3 py-2.5">{{ __('Tenure Left') }}</th>
                        <th class="px-3 py-2.5 text-center">{{ __('Today\'s Status') }}</th>
                        <th class="px-3 py-2.5 text-center">{{ __('Action') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800 font-medium">
                    @forelse ($cashSplit['financiers'] ?? [] as $f)
                    <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/40 transition-colors">
                        <td class="px-3 py-2">
                            <a href="{{ route('financiers.show', $f['id']) }}" class="font-semibold text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ $f['name'] }}
                            </a>
                            @if (!empty($f['phone']))
                                <span class="text-[11px] text-zinc-400 block">{{ $f['phone'] }}</span>
                            @endif
                        </td>
                        <td class="px-3 py-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-semibold {{ match($f['payout_type']) { 'daily' => 'bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300', 'weekly' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300', default => 'bg-purple-100 text-purple-800 dark:bg-purple-950 dark:text-purple-300' } }}">
                                {{ ucfirst($f['payout_type']) }}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-right font-semibold text-zinc-900 dark:text-zinc-100">
                            ₹{{ number_format($f['installment_amount'], 2) }}
                        </td>
                        <td class="px-3 py-2 text-right font-bold {{ $f['paid_today'] > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-zinc-400' }}">
                            {{ $f['paid_today'] > 0 ? '₹' . number_format($f['paid_today'], 2) : '—' }}
                        </td>
                        <td class="px-3 py-2 text-right text-orange-600 dark:text-orange-400 font-semibold">
                            ₹{{ number_format($f['outstanding_balance'], 2) }}
                        </td>
                        <td class="px-3 py-2">
                            @if (!empty($f['remaining_term']))
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-[11px] font-medium bg-sky-50 text-sky-700 dark:bg-sky-950/50 dark:text-sky-300">
                                    {{ $f['remaining_term'] }}
                                </span>
                            @else
                                <span class="text-zinc-400">—</span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            @if ($f['is_paid_today'])
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300">
                                    {{ __('PAID TODAY ✅') }}
                                </span>
                            @elseif ($f['is_partially_paid_today'])
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300">
                                    {{ __('PARTIAL') }}
                                </span>
                            @elseif ($f['payout_type'] === 'daily')
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300">
                                    {{ __('DUE TODAY') }}
                                </span>
                            @else
                                <span class="px-2 py-0.5 rounded-full text-[10px] font-medium text-zinc-500 bg-zinc-100 dark:bg-zinc-800">
                                    {{ $f['due_date_human'] }}
                                </span>
                            @endif
                        </td>
                        <td class="px-3 py-2 text-center">
                            <a href="{{ route('financiers.show', $f['id']) }}" class="text-xs text-indigo-600 dark:text-indigo-400 hover:underline">
                                {{ __('Ledger →') }}
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-zinc-500">{{ __('No active financiers found.') }}</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
