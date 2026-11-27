<?php

namespace App\Services;

use App\Models\Expense;
use App\Models\Financier;
use App\Models\FinancierPayment;
use App\Models\ManualRecord;
use App\Models\Sale;
use App\Models\SupplierPayment;
use App\Models\User;
use Carbon\CarbonInterface;
use DateTimeInterface;
use Illuminate\Support\Carbon;

class DailyCashSplitService
{
    /**
     * Compute the Daily Cash Split and Financier Due breakdown.
     *
     * Rules:
     * - Material Reserve: 72% of Daily Sales (for raw material repurchase)
     * - Financer Pot:     21% of Daily Sales (for loan / financier EMIs)
     * - Home Draw:        7% of Daily Sales (safe household profit draw)
     *
     * @return array<string, mixed>
     */
    public function getSplitData(DateTimeInterface|CarbonInterface|null $date = null, ?User $user = null): array
    {
        $date = $date ?? today();
        $dateString = $date->format('Y-m-d');

        // 1. Total Daily Sales Revenue
        $salesToday = (float) Sale::query()
            ->when($user && $user->isStaff(), fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', $dateString)
            ->sum('total_amount');

        // 2. The Three Cash Split Numbers
        $materialReserve = round($salesToday * 0.72, 2); // 72%
        $financerPot     = round($salesToday * 0.21, 2); // 21%
        $homeDraw        = round($salesToday * 0.07, 2); // 7%

        // 3. Amount Paid to Financiers Today
        $financiersPaidToday = (float) FinancierPayment::query()
            ->when($user && ! $user->isManager() && ! $user->isFinancier(), fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', $dateString)
            ->whereIn('type', [
                'daily_payment',
                'weekly_payment',
                'monthly_payment',
                'loan_repaid',
                'installment_paid',
                'interest_payment',
                'adjusted_balance',
            ])
            ->sum('amount');

        // 4. Daily Expenses (Categorized for Envelope Deductions)
        $expensesToday = Expense::query()
            ->with('category')
            ->when($user && ! $user->isSystemAdmin(), fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', $dateString)
            ->get();

        $suppliersPaidToday = (float) SupplierPayment::query()
            ->when($user && ! $user->isManager(), fn ($q) => $q->where('user_id', $user->id))
            ->whereDate('date', $dateString)
            ->where('type', 'payment_made')
            ->sum('amount');

        // Household & Personal draws (e.g. Household Expenses) -> deducts from Home Draw (7%)
        $householdExpensesToday = (float) $expensesToday->filter(function (Expense $e) {
            $catType = strtolower($e->category?->type ?? '');
            $catName = strtolower($e->category?->name ?? '');
            return $catType === 'household'
                || $catType === 'personal'
                || str_contains($catName, 'household')
                || str_contains($catName, 'personal')
                || str_contains($catName, 'home');
        })->sum('amount');

        // Loan & Financial payments recorded in expenses
        $loanExpensesToday = (float) $expensesToday->filter(function (Expense $e) {
            $catType = strtolower($e->category?->type ?? '');
            $catName = strtolower($e->category?->name ?? '');
            return $catType === 'loan'
                || $catType === 'financial'
                || str_contains($catName, 'loan')
                || str_contains($catName, 'financ');
        })->sum('amount');

        // Operating & Consumables expenses (e.g. Daily Consumables, Raw Materials, shop maintenance)
        $materialExpensesToday = (float) $expensesToday->reject(function (Expense $e) {
            $catType = strtolower($e->category?->type ?? '');
            $catName = strtolower($e->category?->name ?? '');
            return in_array($catType, ['household', 'personal', 'loan', 'financial'], true)
                || str_contains($catName, 'household')
                || str_contains($catName, 'personal')
                || str_contains($catName, 'home')
                || str_contains($catName, 'loan')
                || str_contains($catName, 'financ');
        })->sum('amount');

        // 5. Pot Outflows, Remaining Reserves, and Deficits
        // A. Material Reserve (72%)
        $materialSpentToday = round($materialExpensesToday + $suppliersPaidToday, 2);
        $materialRemaining  = round($materialReserve - $materialSpentToday, 2);

        // B. Financer Pot (21%)
        $totalFinanciersOutflowToday = round($financiersPaidToday + $loanExpensesToday, 2);
        $potRemaining                = round($financerPot - $totalFinanciersOutflowToday, 2);

        // C. Home Draw (7%)
        $homeDrawSpentToday = round($householdExpensesToday, 2);
        $homeDrawRemaining  = round($homeDraw - $homeDrawSpentToday, 2);

        // D. Net Drawer Cash Flow Position Today
        $totalCashOutToday = round($materialSpentToday + $totalFinanciersOutflowToday + $homeDrawSpentToday, 2);
        $netCashFlowToday  = round($salesToday - $totalCashOutToday, 2);

        // 5. Active Financiers and their Due Dates / Schedule
        $financiers = Financier::query()
            ->where('status', 'active')
            ->when($user && ! $user->isManager() && ! $user->isFinancier(), fn ($q) => $q->where('user_id', $user->id))
            ->when($user && $user->isFinancier(), fn ($q) => $q->where('financier_user_id', $user->id))
            ->with(['payments' => fn ($q) => $q->whereDate('date', $dateString)])
            ->orderBy('name')
            ->get();

        // Fetch any scheduled pending manual records for financiers
        $manualDues = ManualRecord::query()
            ->where('party_type', 'financier')
            ->where('status', 'pending')
            ->whereNotNull('financier_id')
            ->get()
            ->keyBy('financier_id');

        $financierList = $financiers->map(function (Financier $f) use ($manualDues) {
            $installment = (float) $f->default_payment_amount;
            $paidToday = (float) $f->payments->sum('amount');
            $balance = (float) $f->outstanding_balance;

            // Determine due date from scheduled manual record or payout frequency
            $manualRecord = $manualDues->get($f->id);
            $dueDate = null;
            $dueDateHuman = null;
            $isDueToday = false;

            if ($manualRecord && $manualRecord->due_date) {
                $dueDate = $manualRecord->due_date;
                $dueDateHuman = $manualRecord->due_date->format('d M Y');
                $isDueToday = $manualRecord->due_date->isToday();
            } else {
                $dueDateHuman = match ($f->payout_type) {
                    'daily' => __('Daily (Today)'),
                    'weekly' => __('Weekly'),
                    'monthly' => __('Monthly'),
                    default => ucfirst($f->payout_type),
                };
                $isDueToday = ($f->payout_type === 'daily');
            }

            $isPaidToday = $installment > 0 && $paidToday >= $installment;
            $isPartiallyPaidToday = $paidToday > 0 && $paidToday < $installment;

            return [
                'id' => $f->id,
                'name' => $f->name,
                'phone' => $f->phone,
                'payout_type' => $f->payout_type,
                'interest_type' => $f->interest_type,
                'installment_amount' => $installment,
                'outstanding_balance' => $balance,
                'paid_today' => $paidToday,
                'due_date' => $dueDate,
                'due_date_human' => $dueDateHuman,
                'is_due_today' => $isDueToday,
                'is_paid_today' => $isPaidToday,
                'is_partially_paid_today' => $isPartiallyPaidToday,
                'remaining_term' => $f->remaining_term,
            ];
        });

        // Financier obligations totals
        $totalDailyInstallments = (float) $financierList->where('payout_type', 'daily')->sum('installment_amount');
        $totalWeeklyInstallments = (float) $financierList->where('payout_type', 'weekly')->sum('installment_amount');
        $totalMonthlyInstallments = (float) $financierList->where('payout_type', 'monthly')->sum('installment_amount');
        $totalOutstanding = (float) $financierList->sum('outstanding_balance');

        return [
            'date' => $date,
            'date_human' => $date->format('d M Y'),
            'sales_today' => $salesToday,
            'material_reserve' => $materialReserve,
            'financer_pot' => $financerPot,
            'home_draw' => $homeDraw,
            'financiers_paid_today' => $totalFinanciersOutflowToday,
            'pot_remaining' => $potRemaining,
            'material_spent_today' => $materialSpentToday,
            'material_remaining' => $materialRemaining,
            'home_draw_spent_today' => $homeDrawSpentToday,
            'home_draw_remaining' => $homeDrawRemaining,
            'total_cash_out_today' => $totalCashOutToday,
            'net_cash_flow_today' => $netCashFlowToday,
            'financier_count' => $financierList->count(),
            'financiers' => $financierList,
            'total_daily_installments' => $totalDailyInstallments,
            'total_weekly_installments' => $totalWeeklyInstallments,
            'total_monthly_installments' => $totalMonthlyInstallments,
            'total_outstanding' => $totalOutstanding,
        ];
    }
}
