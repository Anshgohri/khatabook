<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\FinancierFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $financier_user_id
 * @property string $name
 * @property string|null $phone
 * @property string $payout_type
 * @property string $interest_type
 * @property string $default_payment_amount
 * @property string $outstanding_balance
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property User|null $financierUser
 * @property Collection<int, FinancierPayment> $payments
 */
#[Fillable(['user_id', 'financier_user_id', 'name', 'phone', 'payout_type', 'interest_type', 'default_payment_amount', 'outstanding_balance', 'status', 'notes'])]
class Financier extends Model
{
    /** @use HasFactory<FinancierFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_payment_amount' => 'decimal:2',
            'outstanding_balance' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function financierUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'financier_user_id');
    }

    /**
     * @return HasMany<FinancierPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(FinancierPayment::class);
    }

    /**
     * Recalculate and update the running balance for this financier.
     */
    public function recalculateOutstandingBalance(): void
    {
        $totalLoanReceived = (float) $this->payments()
            ->where('type', 'loan_received')
            ->sum('amount');

        if ($this->interest_type === 'principal_reducing') {
            $totalPrincipalDeducted = (float) $this->payments()
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'adjusted_balance'])
                ->sum('amount');
        } else {
            // Default: interest_only. Installment payments are interest payments, principal remains intact.
            // Only explicit loan_repaid and adjusted_balance payments reduce principal balance.
            $totalPrincipalDeducted = (float) $this->payments()
                ->whereIn('type', ['loan_repaid', 'adjusted_balance'])
                ->sum('amount');
        }

        $this->update([
            'outstanding_balance' => max(0.0, $totalLoanReceived - $totalPrincipalDeducted),
        ]);
    }

    public function getTotalLoanReceivedAttribute(): float
    {
        return (float) $this->payments
            ->where('type', 'loan_received')
            ->sum('amount');
    }

    public function getTotalPaidAttribute(): float
    {
        return (float) $this->payments
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment', 'adjusted_balance'])
            ->sum('amount');
    }

    public function getTotalInterestPaidAttribute(): float
    {
        if ($this->interest_type === 'principal_reducing') {
            return (float) $this->payments
                ->where('type', 'interest_payment')
                ->sum('amount');
        }

        return (float) $this->payments
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'interest_payment'])
            ->sum('amount');
    }

    public function getTotalPrincipalRepaidAttribute(): float
    {
        if ($this->interest_type === 'principal_reducing') {
            return (float) $this->payments
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'adjusted_balance'])
                ->sum('amount');
        }

        return (float) $this->payments
            ->whereIn('type', ['loan_repaid', 'adjusted_balance'])
            ->sum('amount');
    }

    /**
     * Get the effective installment amount per period.
     */
    public function getEffectiveInstallmentAmount(): float
    {
        if ((float) $this->default_payment_amount > 0) {
            return (float) $this->default_payment_amount;
        }

        if ($this->relationLoaded('payments')) {
            $lastPayment = $this->payments
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'installment_paid'])
                ->where('amount', '>', 0)
                ->sortByDesc('date')
                ->first();
        } else {
            $lastPayment = $this->payments()
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'installment_paid'])
                ->where('amount', '>', 0)
                ->latest('date')
                ->first();
        }

        return $lastPayment ? (float) $lastPayment->amount : 0.0;
    }

    /**
     * Get the effective payout type ('daily', 'weekly', 'monthly').
     */
    public function getEffectivePayoutType(): string
    {
        if (! empty($this->payout_type)) {
            return strtolower($this->payout_type);
        }

        if ($this->relationLoaded('payments')) {
            $lastPayment = $this->payments
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment'])
                ->sortByDesc('date')
                ->first();
        } else {
            $lastPayment = $this->payments()
                ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment'])
                ->latest('date')
                ->first();
        }

        return match ($lastPayment?->type) {
            'daily_payment' => 'daily',
            'weekly_payment' => 'weekly',
            'monthly_payment' => 'monthly',
            default => 'monthly',
        };
    }

    /**
     * Calculate remaining installments for principal-reducing loans.
     * Returns null if not principal-reducing or if installment amount is unavailable.
     * Returns 0 if outstanding balance is zero or paid off.
     */
    public function remainingInstallmentsCount(): ?int
    {
        if ($this->interest_type !== 'principal_reducing') {
            return null;
        }

        $balance = (float) $this->outstanding_balance;
        if ($balance <= 0) {
            return 0;
        }

        $installment = $this->getEffectiveInstallmentAmount();
        if ($installment <= 0) {
            return null;
        }

        return (int) ceil($balance / $installment);
    }

    /**
     * Get a formatted remaining term label (e.g. "10 months left", "4 weeks left", "12 days left", or "Paid Off").
     */
    public function remainingTermLabel(): ?string
    {
        if ($this->interest_type !== 'principal_reducing') {
            return null;
        }

        $balance = (float) $this->outstanding_balance;
        if ($balance <= 0) {
            return __('Paid Off');
        }

        $count = $this->remainingInstallmentsCount();
        if ($count === null) {
            return null;
        }

        $type = $this->getEffectivePayoutType();
        $unit = match ($type) {
            'daily' => Str::plural('day', $count),
            'weekly' => Str::plural('week', $count),
            'monthly' => Str::plural('month', $count),
            default => Str::plural('period', $count),
        };

        return "{$count} {$unit} left";
    }

    public function getRemainingTermAttribute(): ?string
    {
        return $this->remainingTermLabel();
    }

    public function getRemainingInstallmentsCountAttribute(): ?int
    {
        return $this->remainingInstallmentsCount();
    }
}
