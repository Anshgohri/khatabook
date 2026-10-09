<?php

use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Providers\AppServiceProvider;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

new #[Title('Product Sales Analytics & Report')] class extends Component {
    use WithPagination;

    #[Url]
    public string $preset = 'this_month';

    #[Url]
    public string $startDate = '';

    #[Url]
    public string $endDate = '';

    #[Url]
    public string $productId = '';

    #[Url]
    public string $categoryId = '';

    #[Url]
    public string $search = '';

    #[Url]
    public string $sortBy = 'quantity_desc';

    #[Url]
    public string $groupBy = 'day';

    public function mount(): void
    {
        $this->authorize('viewAny', Sale::class);

        if (empty($this->startDate)) {
            $this->startDate = now()->startOfMonth()->toDateString();
        }
        if (empty($this->endDate)) {
            $this->endDate = now()->toDateString();
        }
    }

    public function updatedPreset(string $val): void
    {
        switch ($val) {
            case 'today':
                $this->startDate = now()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'yesterday':
                $this->startDate = now()->subDay()->toDateString();
                $this->endDate = now()->subDay()->toDateString();
                break;
            case 'this_month':
                $this->startDate = now()->startOfMonth()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'last_month':
                $this->startDate = now()->subMonth()->startOfMonth()->toDateString();
                $this->endDate = now()->subMonth()->endOfMonth()->toDateString();
                break;
            case 'this_year':
                $this->startDate = now()->startOfYear()->toDateString();
                $this->endDate = now()->toDateString();
                break;
            case 'all_time':
                $this->startDate = '2000-01-01';
                $this->endDate = now()->addYear()->toDateString();
                break;
        }

        $this->resetPage();
    }

    public function updating(string $property): void
    {
        if (in_array($property, ['search', 'productId', 'categoryId', 'sortBy', 'startDate', 'endDate', 'groupBy'], true)) {
            $this->resetPage();
        }
    }

    #[Computed]
    public function dateRange(): array
    {
        if ($this->preset === 'custom' && ! empty($this->startDate) && ! empty($this->endDate)) {
            return [
                Carbon::parse($this->startDate)->startOfDay(),
                Carbon::parse($this->endDate)->endOfDay(),
            ];
        }

        return match ($this->preset) {
            'today' => [now()->startOfDay(), now()->endOfDay()],
            'yesterday' => [now()->subDay()->startOfDay(), now()->subDay()->endOfDay()],
            'this_month' => [now()->startOfMonth(), now()->endOfMonth()],
            'last_month' => [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()],
            'this_year' => [now()->startOfYear(), now()->endOfYear()],
            'all_time' => [Carbon::parse('2000-01-01'), now()->addYear()],
            default => [
                ! empty($this->startDate) ? Carbon::parse($this->startDate)->startOfDay() : now()->startOfMonth(),
                ! empty($this->endDate) ? Carbon::parse($this->endDate)->endOfDay() : now()->endOfDay(),
            ],
        };
    }

    #[Computed]
    public function categories()
    {
        return ProductCategory::orderBy('name')->get();
    }

    #[Computed]
    public function productsList()
    {
        return Product::orderBy('name')->get();
    }

    /**
     * Get aggregated product sales metrics across the selected date range and filters.
     */
    #[Computed]
    public function productSalesMetrics()
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        // Query line items from sales within the date range
        $query = SaleItem::query()
            ->with(['product.category', 'sale'])
            ->whereHas('sale', function ($q) use ($from, $to, $user) {
                $q->whereBetween('date', [$from, $to])
                    ->when($user->isStaff(), fn ($sq) => $sq->where('user_id', $user->id));
            });

        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        if ($this->categoryId) {
            $query->whereHas('product', fn ($pq) => $pq->where('product_category_id', $this->categoryId));
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($sub) use ($search) {
                $sub->whereHas('product', fn ($pq) => $pq->where('name', AppServiceProvider::likeOperator(), "%{$search}%")->orWhere('sku', AppServiceProvider::likeOperator(), "%{$search}%"))
                    ->orWhereHas('sale', fn ($sq) => $sq->where('items_sold', AppServiceProvider::likeOperator(), "%{$search}%"));
            });
        }

        $items = $query->get();

        // Group by product_id (or product name for items without product_id)
        $grouped = $items->groupBy(fn ($item) => $item->product_id ? 'product_'.$item->product_id : 'custom_'.($item->product?->name ?? $item->sale?->items_sold ?? 'Uncategorized'));

        $report = $grouped->map(function ($group) {
            $first = $group->first();
            $product = $first->product;

            $totalQty = (int) $group->sum('quantity');
            $totalRevenue = (float) $group->sum('total_price');
            $totalCost = (float) $group->sum(fn ($item) => $item->totalCost());
            $totalProfit = (float) ($totalRevenue - $totalCost);
            $avgPrice = $totalQty > 0 ? $totalRevenue / $totalQty : 0.0;
            $transactionCount = $group->pluck('sale_id')->unique()->count();
            $lastSold = $group->map(fn ($item) => $item->sale?->date)->filter()->max();

            return [
                'product_id' => $product?->id,
                'name' => $product?->name ?? ($first->sale?->items_sold ?? 'Custom Item'),
                'sku' => $product?->sku ?? 'N/A',
                'category' => $product?->category?->name ?? 'General',
                'stock_level' => $product?->stock_level,
                'unit' => $product?->unit ?? 'Pcs',
                'total_quantity' => $totalQty,
                'total_revenue' => $totalRevenue,
                'total_cost' => $totalCost,
                'total_profit' => $totalProfit,
                'profit_margin' => $totalRevenue > 0 ? ($totalProfit / $totalRevenue) * 100 : 0.0,
                'avg_unit_price' => $avgPrice,
                'transaction_count' => $transactionCount,
                'last_sold_at' => $lastSold ? $lastSold->format('d M Y') : 'N/A',
            ];
        });

        // Apply sorting
        return $report->sort(function ($a, $b) {
            return match ($this->sortBy) {
                'revenue_desc' => $b['total_revenue'] <=> $a['total_revenue'],
                'profit_desc' => $b['total_profit'] <=> $a['total_profit'],
                'name_asc' => strcasecmp($a['name'], $b['name']),
                default => $b['total_quantity'] <=> $a['total_quantity'],
            };
        })->values();
    }

    /**
     * Overall Summary KPIs.
     */
    #[Computed]
    public function summaryStats(): array
    {
        $metrics = $this->productSalesMetrics;

        $totalQty = $metrics->sum('total_quantity');
        $totalRevenue = $metrics->sum('total_revenue');
        $totalProfit = $metrics->sum('total_profit');
        $avgPrice = $totalQty > 0 ? $totalRevenue / $totalQty : 0.0;

        $topProduct = $metrics->sortByDesc('total_revenue')->first();

        return [
            'total_quantity' => $totalQty,
            'total_revenue' => $totalRevenue,
            'total_profit' => $totalProfit,
            'avg_price' => $avgPrice,
            'top_product' => $topProduct ? $topProduct['name'].' ('.$topProduct['total_quantity'].' '.$topProduct['unit'].' - ₹'.number_format($topProduct['total_revenue'], 2).')' : 'N/A',
        ];
    }

    /**
     * Timeline breakdown (Day / Month / Year sales velocity).
     */
    #[Computed]
    public function timelineBreakdown()
    {
        [$from, $to] = $this->dateRange;
        $user = Auth::user();

        $query = SaleItem::query()
            ->with(['sale', 'product'])
            ->whereHas('sale', function ($q) use ($from, $to, $user) {
                $q->whereBetween('date', [$from, $to])
                    ->when($user->isStaff(), fn ($sq) => $sq->where('user_id', $user->id));
            });

        if ($this->productId) {
            $query->where('product_id', $this->productId);
        }

        if ($this->categoryId) {
            $query->whereHas('product', fn ($pq) => $pq->where('product_category_id', $this->categoryId));
        }

        $items = $query->get();

        $grouped = $items->groupBy(function ($item) {
            $date = $item->sale?->date;
            if (! $date) {
                return 'Unknown';
            }

            return match ($this->groupBy) {
                'month' => $date->format('F Y'),
                'year' => $date->format('Y'),
                default => $date->format('Y-m-d (D)'),
            };
        });

        return $grouped->map(function ($group, $periodKey) {
            $totalQty = (int) $group->sum('quantity');
            $totalRevenue = (float) $group->sum('total_price');
            $totalCost = (float) $group->sum(fn ($i) => $i->totalCost());
            $totalProfit = (float) ($totalRevenue - $totalCost);
            $avgPrice = $totalQty > 0 ? $totalRevenue / $totalQty : 0.0;

            return [
                'period' => $periodKey,
                'quantity' => $totalQty,
                'revenue' => $totalRevenue,
                'profit' => $totalProfit,
                'avg_price' => $avgPrice,
                'item_count' => $group->count(),
            ];
        })->values();
    }

    /**
     * Export Product Sales Analytics report to CSV.
     */
    public function exportCsv()
    {
        $this->authorize('viewAny', Sale::class);

        $data = $this->productSalesMetrics;
        $isAdmin = Auth::user()?->isAdmin();

        return response()->streamDownload(function () use ($data, $isAdmin) {
            $handle = fopen('php://output', 'w');

            $headers = [
                'Product Name',
                'Category',
                'SKU',
                'Current Stock',
                'Total Quantity Sold',
                'Total Sales Revenue (INR)',
                'Average Selling Price (INR)',
                'Transactions Count',
                'Last Sold Date',
            ];

            if ($isAdmin) {
                $headers[] = 'Production Cost (INR)';
                $headers[] = 'Gross Profit (INR)';
                $headers[] = 'Profit Margin (%)';
            }

            fputcsv($handle, $headers);

            foreach ($data as $row) {
                $line = [
                    $row['name'],
                    $row['category'],
                    $row['sku'],
                    $row['stock_level'] ?? 'N/A',
                    $row['total_quantity'],
                    number_format($row['total_revenue'], 2, '.', ''),
                    number_format($row['avg_unit_price'], 2, '.', ''),
                    $row['transaction_count'],
                    $row['last_sold_at'],
                ];

                if ($isAdmin) {
                    $line[] = number_format($row['total_cost'], 2, '.', '');
                    $line[] = number_format($row['total_profit'], 2, '.', '');
                    $line[] = number_format($row['profit_margin'], 2, '.', '').'%';
                }

                fputcsv($handle, $line);
            }

            fclose($handle);
        }, 'product-sales-report-'.$this->preset.'-'.now()->format('Y-m-d').'.csv');
    }
}; ?>

<div class="flex flex-col gap-6">
    <!-- Header Title & Export Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
            <div class="flex items-center gap-2">
                <flux:heading size="xl">{{ __('Product Sales Analytics & Report') }}</flux:heading>
                <span class="px-2.5 py-0.5 rounded-full text-xs font-black bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 border border-emerald-500/40 uppercase">
                    📊 {{ __('Item Breakdown') }}
                </span>
            </div>
            <flux:subheading>{{ __('Track total units sold, sales revenue, profitability, and date-wise trends for every product.') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <flux:button icon="arrow-down-tray" variant="primary" wire:click="exportCsv">
                {{ __('Export Analytics CSV') }}
            </flux:button>
        </div>
    </div>

    <!-- Filter Control Panel -->
    <div class="bg-white dark:bg-zinc-900 p-5 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm flex flex-col gap-4">
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
            <!-- Preset Range -->
            <div>
                <flux:label>{{ __('Time Period Preset') }}</flux:label>
                <flux:select wire:model.live="preset" class="mt-1">
                    <flux:select.option value="today">{{ __('Today') }}</flux:select.option>
                    <flux:select.option value="yesterday">{{ __('Yesterday') }}</flux:select.option>
                    <flux:select.option value="this_month">{{ __('This Month') }}</flux:select.option>
                    <flux:select.option value="last_month">{{ __('Last Month') }}</flux:select.option>
                    <flux:select.option value="this_year">{{ __('This Year') }}</flux:select.option>
                    <flux:select.option value="custom">{{ __('Custom Date Range') }}</flux:select.option>
                    <flux:select.option value="all_time">{{ __('All Time') }}</flux:select.option>
                </flux:select>
            </div>

            <!-- Start Date -->
            <div>
                <flux:label>{{ __('From Date') }}</flux:label>
                <flux:input type="date" wire:model.live="startDate" class="mt-1" :disabled="$preset !== 'custom'" />
            </div>

            <!-- End Date -->
            <div>
                <flux:label>{{ __('To Date') }}</flux:label>
                <flux:input type="date" wire:model.live="endDate" class="mt-1" :disabled="$preset !== 'custom'" />
            </div>

            <!-- Category Filter -->
            <div>
                <flux:label>{{ __('Category') }}</flux:label>
                <flux:select wire:model.live="categoryId" class="mt-1">
                    <flux:select.option value="">{{ __('All Categories') }}</flux:select.option>
                    @foreach($this->categories as $cat)
                        <flux:select.option value="{{ $cat->id }}">{{ $cat->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <!-- Product Filter -->
            <div>
                <flux:label>{{ __('Select Product') }}</flux:label>
                <flux:select wire:model.live="productId" class="mt-1">
                    <flux:select.option value="">{{ __('All Products Summary') }}</flux:select.option>
                    @foreach($this->productsList as $prod)
                        <flux:select.option value="{{ $prod->id }}">{{ $prod->name }}</flux:select.option>
                    @endforeach
                </flux:select>
            </div>

            <!-- Search Keyword -->
            <div>
                <flux:label>{{ __('Search Product / SKU') }}</flux:label>
                <flux:input icon="magnifying-glass" wire:model.live.debounce.300ms="search" placeholder="Type product name or SKU..." class="mt-1" />
            </div>

            <!-- Sort By -->
            <div>
                <flux:label>{{ __('Sort By') }}</flux:label>
                <flux:select wire:model.live="sortBy" class="mt-1">
                    <flux:select.option value="quantity_desc">{{ __('Most Quantity Sold') }}</flux:select.option>
                    <flux:select.option value="revenue_desc">{{ __('Highest Revenue (₹)') }}</flux:select.option>
                    @if (auth()->user()?->isAdmin())
                        <flux:select.option value="profit_desc">{{ __('Highest Profit (₹)') }}</flux:select.option>
                    @endif
                    <flux:select.option value="name_asc">{{ __('Product Name (A-Z)') }}</flux:select.option>
                </flux:select>
            </div>
        </div>
    </div>

    <!-- Summary KPI Cards Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Total Quantity Sold -->
        <div class="p-5 rounded-2xl border border-blue-200 dark:border-blue-900/60 bg-blue-50/50 dark:bg-blue-950/30 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-blue-700 dark:text-blue-300 uppercase tracking-wider">📦 {{ __('Total Units Sold') }}</span>
                <span class="p-1.5 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400">📊</span>
            </div>
            <div class="text-2xl font-black text-blue-950 dark:text-blue-100">
                {{ number_format($this->summaryStats['total_quantity']) }} <span class="text-sm font-semibold text-blue-600 dark:text-blue-300">units</span>
            </div>
            <div class="text-[11px] text-blue-600 dark:text-blue-400">
                In period: {{ \Carbon\Carbon::parse($this->startDate)->format('d M Y') }} – {{ \Carbon\Carbon::parse($this->endDate)->format('d M Y') }}
            </div>
        </div>

        <!-- Total Sales Revenue -->
        <div class="p-5 rounded-2xl border border-emerald-200 dark:border-emerald-900/60 bg-emerald-50/50 dark:bg-emerald-950/30 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-emerald-700 dark:text-emerald-300 uppercase tracking-wider">💰 {{ __('Total Sales Revenue') }}</span>
                <span class="p-1.5 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">💵</span>
            </div>
            <div class="text-2xl font-black text-emerald-950 dark:text-emerald-100">
                ₹{{ number_format($this->summaryStats['total_revenue'], 2) }}
            </div>
            <div class="text-[11px] text-emerald-600 dark:text-emerald-400">
                Avg price: ₹{{ number_format($this->summaryStats['avg_price'], 2) }} / unit
            </div>
        </div>

        <!-- Total Profit (Admin only) -->
        @if (auth()->user()?->isAdmin())
        <div class="p-5 rounded-2xl border border-amber-200 dark:border-amber-900/60 bg-amber-50/50 dark:bg-amber-950/30 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-amber-700 dark:text-amber-300 uppercase tracking-wider">📈 {{ __('Total Sales Profit') }}</span>
                <span class="p-1.5 rounded-lg bg-amber-500/10 text-amber-600 dark:text-amber-400">🚀</span>
            </div>
            <div class="text-2xl font-black text-amber-950 dark:text-amber-100">
                ₹{{ number_format($this->summaryStats['total_profit'], 2) }}
            </div>
            <div class="text-[11px] text-amber-600 dark:text-amber-400">
                Gross margin: {{ $this->summaryStats['total_revenue'] > 0 ? number_format(($this->summaryStats['total_profit'] / $this->summaryStats['total_revenue']) * 100, 1) : 0 }}%
            </div>
        </div>
        @else
        <div class="p-5 rounded-2xl border border-purple-200 dark:border-purple-900/60 bg-purple-50/50 dark:bg-purple-950/30 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-purple-700 dark:text-purple-300 uppercase tracking-wider">🏷️ {{ __('Avg Selling Price') }}</span>
                <span class="p-1.5 rounded-lg bg-purple-500/10 text-purple-600 dark:text-purple-400">🏷️</span>
            </div>
            <div class="text-2xl font-black text-purple-950 dark:text-purple-100">
                ₹{{ number_format($this->summaryStats['avg_price'], 2) }}
            </div>
            <div class="text-[11px] text-purple-600 dark:text-purple-400">
                Per unit average across period
            </div>
        </div>
        @endif

        <!-- Top Selling Product -->
        <div class="p-5 rounded-2xl border border-indigo-200 dark:border-indigo-900/60 bg-indigo-50/50 dark:bg-indigo-950/30 shadow-sm flex flex-col gap-2">
            <div class="flex items-center justify-between">
                <span class="text-xs font-bold text-indigo-700 dark:text-indigo-300 uppercase tracking-wider">🏆 {{ __('Top Best Seller') }}</span>
                <span class="p-1.5 rounded-lg bg-indigo-500/10 text-indigo-600 dark:text-indigo-400">👑</span>
            </div>
            <div class="text-sm font-extrabold text-indigo-950 dark:text-indigo-100 truncate">
                {{ $this->summaryStats['top_product'] }}
            </div>
            <div class="text-[11px] text-indigo-600 dark:text-indigo-400">
                Highest revenue product in period
            </div>
        </div>
    </div>

    <!-- Main Table: Product-wise Aggregated Sales Report -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden flex flex-col gap-4 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <h3 class="font-extrabold text-lg text-zinc-900 dark:text-white">{{ __('Product Sales Breakdown') }}</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('Detailed summary of quantity sold, total revenue, and profitability per product item.') }}</p>
            </div>
            <div class="text-xs font-semibold text-zinc-500 dark:text-zinc-400">
                Showing {{ $this->productSalesMetrics->count() }} product items
            </div>
        </div>

        <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm text-left text-zinc-800 dark:text-zinc-200">
                <thead class="text-xs uppercase bg-zinc-100 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-300 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3">{{ __('Product Details') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Units Sold') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Total Sales Revenue') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Avg Price / Unit') }}</th>
                        @if (auth()->user()?->isAdmin())
                            <th class="px-4 py-3 text-end">{{ __('Gross Profit') }}</th>
                            <th class="px-4 py-3 text-center">{{ __('Margin') }}</th>
                        @endif
                        <th class="px-4 py-3 text-center">{{ __('Transactions') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Last Sold Date') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($this->productSalesMetrics as $item)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <!-- Product Details -->
                        <td class="px-4 py-3">
                            <div class="font-bold text-zinc-900 dark:text-white">{{ $item['name'] }}</div>
                            <div class="flex items-center gap-2 mt-0.5 text-xs text-zinc-500 dark:text-zinc-400">
                                <span class="px-1.5 py-0.5 rounded bg-zinc-100 dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 font-medium">
                                    {{ $item['category'] }}
                                </span>
                                @if ($item['sku'] !== 'N/A')
                                    <span>SKU: {{ $item['sku'] }}</span>
                                @endif
                                @if ($item['stock_level'] !== null)
                                    <span class="text-emerald-600 dark:text-emerald-400 font-semibold">Stock: {{ $item['stock_level'] }} {{ $item['unit'] }}</span>
                                @endif
                            </div>
                        </td>

                        <!-- Units Sold -->
                        <td class="px-4 py-3 text-center">
                            <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full font-black text-xs bg-blue-100 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-300 dark:border-blue-800">
                                {{ number_format($item['total_quantity']) }} {{ $item['unit'] }}
                            </span>
                        </td>

                        <!-- Total Revenue -->
                        <td class="px-4 py-3 text-end font-extrabold text-emerald-600 dark:text-emerald-400 text-base">
                            ₹{{ number_format($item['total_revenue'], 2) }}
                        </td>

                        <!-- Avg Unit Price -->
                        <td class="px-4 py-3 text-end font-semibold text-zinc-700 dark:text-zinc-300">
                            ₹{{ number_format($item['avg_unit_price'], 2) }}
                        </td>

                        <!-- Gross Profit & Margin (Admin only) -->
                        @if (auth()->user()?->isAdmin())
                            <td class="px-4 py-3 text-end font-extrabold {{ $item['total_profit'] >= 0 ? 'text-amber-600 dark:text-amber-400' : 'text-red-600 dark:text-red-400' }}">
                                ₹{{ number_format($item['total_profit'], 2) }}
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="px-2 py-0.5 rounded text-xs font-bold {{ $item['profit_margin'] >= 20 ? 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950 dark:text-emerald-300' : ($item['profit_margin'] >= 0 ? 'bg-amber-100 text-amber-800 dark:bg-amber-950 dark:text-amber-300' : 'bg-red-100 text-red-800 dark:bg-red-950 dark:text-red-300') }}">
                                    {{ number_format($item['profit_margin'], 1) }}%
                                </span>
                            </td>
                        @endif

                        <!-- Transactions -->
                        <td class="px-4 py-3 text-center font-semibold text-zinc-600 dark:text-zinc-400">
                            {{ $item['transaction_count'] }} {{ __('sales') }}
                        </td>

                        <!-- Last Sold Date -->
                        <td class="px-4 py-3 text-end text-xs text-zinc-500 dark:text-zinc-400 whitespace-nowrap">
                            {{ $item['last_sold_at'] }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()?->isAdmin() ? 8 : 6 }}" class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                            {{ __('No product sales records found for the selected filter criteria.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Section 2: Date-wise / Timeline Sales Trend Breakdown -->
    <div class="bg-white dark:bg-zinc-900 rounded-2xl border border-zinc-200 dark:border-zinc-700 shadow-sm overflow-hidden flex flex-col gap-4 p-5">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 pb-3 border-b border-zinc-200 dark:border-zinc-800">
            <div>
                <h3 class="font-extrabold text-lg text-zinc-900 dark:text-white">{{ __('Timeline Sales Breakdown (Daily / Monthly / Yearly)') }}</h3>
                <p class="text-xs text-zinc-500 dark:text-zinc-400">{{ __('View sales velocity and volume breakdown aggregated across time intervals.') }}</p>
            </div>

            <div class="flex items-center gap-2">
                <flux:label size="sm">{{ __('Group Interval:') }}</flux:label>
                <flux:select wire:model.live="groupBy" class="w-36">
                    <flux:select.option value="day">{{ __('Daily') }}</flux:select.option>
                    <flux:select.option value="month">{{ __('Monthly') }}</flux:select.option>
                    <flux:select.option value="year">{{ __('Yearly') }}</flux:select.option>
                </flux:select>
            </div>
        </div>

        <div class="w-full overflow-x-auto rounded-xl border border-zinc-200 dark:border-zinc-700">
            <table class="w-full text-sm text-left text-zinc-800 dark:text-zinc-200">
                <thead class="text-xs uppercase bg-zinc-100 dark:bg-zinc-800/80 text-zinc-600 dark:text-zinc-300 border-b border-zinc-200 dark:border-zinc-700">
                    <tr>
                        <th class="px-4 py-3">{{ __('Period / Date') }}</th>
                        <th class="px-4 py-3 text-center">{{ __('Quantity Sold') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Sales Revenue') }}</th>
                        <th class="px-4 py-3 text-end">{{ __('Avg Price / Unit') }}</th>
                        @if (auth()->user()?->isAdmin())
                            <th class="px-4 py-3 text-end">{{ __('Period Gross Profit') }}</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-800">
                    @forelse ($this->timelineBreakdown as $row)
                    <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50 transition-colors">
                        <td class="px-4 py-3 font-bold text-zinc-900 dark:text-white">
                            📅 {{ $row['period'] }}
                        </td>
                        <td class="px-4 py-3 text-center font-bold text-blue-600 dark:text-blue-400">
                            {{ number_format($row['quantity']) }} units
                        </td>
                        <td class="px-4 py-3 text-end font-extrabold text-emerald-600 dark:text-emerald-400">
                            ₹{{ number_format($row['revenue'], 2) }}
                        </td>
                        <td class="px-4 py-3 text-end font-semibold text-zinc-700 dark:text-zinc-300">
                            ₹{{ number_format($row['avg_price'], 2) }}
                        </td>
                        @if (auth()->user()?->isAdmin())
                            <td class="px-4 py-3 text-end font-extrabold text-amber-600 dark:text-amber-400">
                                ₹{{ number_format($row['profit'], 2) }}
                            </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ auth()->user()?->isAdmin() ? 5 : 4 }}" class="text-center py-8 text-zinc-500 dark:text-zinc-400">
                            {{ __('No timeline sales data available for this range.') }}
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
