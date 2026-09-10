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

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $financier_user_id
 * @property string $name
 * @property string|null $phone
 * @property string $payout_type
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
#[Fillable(['user_id', 'financier_user_id', 'name', 'phone', 'payout_type', 'default_payment_amount', 'outstanding_balance', 'status', 'notes'])]
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

        $totalPaid = (float) $this->payments()
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
            ->sum('amount');

        $this->update([
            'outstanding_balance' => $totalLoanReceived - $totalPaid,
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
            ->whereIn('type', ['daily_payment', 'weekly_payment', 'monthly_payment', 'loan_repaid', 'interest_payment'])
            ->sum('amount');
    }
}
