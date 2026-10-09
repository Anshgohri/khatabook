<?php

namespace App\Http\Controllers;

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
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        if ($user->isFinancier()) {
            return redirect()->route('financiers');
        }
        if ($user->isCustomer()) {
            return redirect()->route('my-orders');
        }
        if ($user->isEmployee()) {
            return redirect()->route('products');
        }

        $salesToday = (float) $this->scopedSales()->whereDate('date', today())->sum('total_amount');

        $profitToday = (float) $this->scopedSales()
            ->whereDate('date', today())
            ->with('items.product')
            ->get()
            ->sum(fn ($sale) => $sale->profit());

        $salesMonth = (float) $this->scopedSales()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('total_amount');

        $profitMonth = (float) $this->scopedSales()
            ->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])
            ->with('items.product')
            ->get()
            ->sum(fn ($sale) => $sale->profit());

        $salesYear = (float) $this->scopedSales()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('total_amount');

        $expensesMonth = (float) $this->scopedExpenses()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

        $profitMargin = $salesMonth > 0
            ? round((($salesMonth - $expensesMonth) / $salesMonth) * 100, 1)
            : 0.0;

        $cashFlow = $salesMonth - $expensesMonth;

        $financiersPaidToday = (float) $this->scopedFinancierPayments()->whereDate('date', today())->sum('amount');

        $financiersPaidMonth = (float) $this->scopedFinancierPayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

        $financiersPaidYear = (float) $this->scopedFinancierPayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');

        $financiersOutstanding = (float) Financier::query()
            ->when(! $user->isManager() && ! $user->isFinancier(), fn ($query) => $query->where('user_id', $user->id))
            ->when($user->isFinancier(), fn ($query) => $query->where('financier_user_id', $user->id))
            ->sum('outstanding_balance');

        $suppliersPaidToday = (float) $this->scopedSupplierPayments()->whereDate('date', today())->sum('amount');

        $suppliersPaidMonth = (float) $this->scopedSupplierPayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

        $suppliersPaidYear = (float) $this->scopedSupplierPayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');

        $suppliersOutstanding = (float) Supplier::query()
            ->when(! $user->isManager(), fn ($query) => $query->where('user_id', $user->id))
            ->sum('outstanding_balance');

        $employeesPaidToday = (float) $this->scopedEmployeePayments()->whereDate('date', today())->sum('amount');

        $employeesPaidMonth = (float) $this->scopedEmployeePayments()->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()])->sum('amount');

        $employeesPaidYear = (float) $this->scopedEmployeePayments()->whereBetween('date', [now()->startOfYear(), now()->endOfYear()])->sum('amount');

        $unreadNotificationsCount = $user->unreadNotifications()->count();
        $recentNotifications = $user->notifications()->latest()->limit(8)->get();

        $chartsPayload = [
            'salesTrend' => $this->salesTrendData(),
            'expenseBreakdown' => $this->expenseBreakdownData(),
            'topItems' => $this->topItemsData(),
        ];

        return view('dashboard.dashboard', compact(
            'salesToday', 'profitToday', 'salesMonth', 'profitMonth', 'salesYear',
            'expensesMonth', 'profitMargin', 'cashFlow',
            'financiersPaidToday', 'financiersPaidMonth', 'financiersPaidYear', 'financiersOutstanding',
            'suppliersPaidToday', 'suppliersPaidMonth', 'suppliersPaidYear', 'suppliersOutstanding',
            'employeesPaidToday', 'employeesPaidMonth', 'employeesPaidYear',
            'unreadNotificationsCount', 'recentNotifications', 'chartsPayload'
        ));
    }

    public function markAllNotificationsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    public function refreshStats()
    {
        CacheService::invalidateUser(Auth::id());
        return redirect()->back();
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

    protected function salesTrendData(): array
    {
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
            'labels' => $rows->map(fn($row) => $row->category->name)->all(),
            'values' => $rows->map(fn($row) => (float) $row->total)->all(),
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
            'values' => $rows->pluck('total')->map(fn($value) => (float) $value)->all(),
        ];
    }
}
