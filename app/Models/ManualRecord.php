<?php

namespace App\Models;

use App\Concerns\Auditable;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Date;

#[Fillable([
    'user_id',
    'party_type',
    'party_name',
    'party_phone',
    'supplier_id',
    'financier_id',
    'employee_id',
    'amount',
    'due_date',
    'frequency',
    'priority',
    'status',
    'paid_amount',
    'paid_date',
    'payment_method',
    'bank_account_id',
    'notes',
    'is_recurring',
])]
class ManualRecord extends Model
{
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'is_recurring' => 'boolean',
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
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    /**
     * @return BelongsTo<Financier, $this>
     */
    public function financier(): BelongsTo
    {
        return $this->belongsTo(Financier::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<BankAccount, $this>
     */
    public function bankAccount(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class);
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Scopes
    // ─────────────────────────────────────────────────────────────────────────

    public function scopePending(Builder $query): Builder
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid(Builder $query): Builder
    {
        return $query->where('status', 'paid');
    }

    public function scopeDueToday(Builder $query): Builder
    {
        return $query->where('status', 'pending')->whereDate('due_date', Date::today());
    }

    public function scopeOverdue(Builder $query): Builder
    {
        return $query->where('status', 'pending')->whereDate('due_date', '<', Date::today());
    }

    // ─────────────────────────────────────────────────────────────────────────
    // Status & Date Helpers
    // ─────────────────────────────────────────────────────────────────────────

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function isDueToday(): bool
    {
        return $this->isPending() && $this->due_date && $this->due_date->isToday();
    }

    public function isOverdue(): bool
    {
        return $this->isPending() && $this->due_date && $this->due_date->isPast() && ! $this->due_date->isToday();
    }

    public function nextDueDate(): CarbonInterface
    {
        $baseDate = $this->due_date ?: Date::today();

        return match ($this->frequency) {
            'daily' => $baseDate->copy()->addDay(),
            'weekly' => $baseDate->copy()->addWeek(),
            'monthly' => $baseDate->copy()->addMonth(),
            default => $baseDate->copy()->addDay(),
        };
    }

    /**
     * Mark this record as paid and optionally schedule the next recurring instance.
     */
    public function markAsPaid(
        ?float $paidAmount = null,
        string $paymentMethod = 'cash',
        ?int $bankAccountId = null,
        ?string $paidDate = null,
        bool $advanceNext = true
    ): ?self {
        $amount = $paidAmount ?? (float) $this->amount;
        $date = $paidDate ?? Date::today()->toDateString();

        $this->update([
            'status' => 'paid',
            'paid_amount' => $amount,
            'paid_date' => $date,
            'payment_method' => $paymentMethod,
            'bank_account_id' => $bankAccountId,
        ]);

        if ($advanceNext && $this->is_recurring && $this->frequency !== 'one_time') {
            return self::create([
                'user_id' => $this->user_id,
                'party_type' => $this->party_type,
                'party_name' => $this->party_name,
                'party_phone' => $this->party_phone,
                'supplier_id' => $this->supplier_id,
                'financier_id' => $this->financier_id,
                'employee_id' => $this->employee_id,
                'amount' => $this->amount,
                'due_date' => $this->nextDueDate()->toDateString(),
                'frequency' => $this->frequency,
                'priority' => $this->priority,
                'status' => 'pending',
                'paid_amount' => 0,
                'notes' => $this->notes,
                'is_recurring' => true,
            ]);
        }

        return null;
    }
}
