<?php

namespace App\Models;

use App\Concerns\Auditable;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'bank_name',
    'account_name',
    'account_number',
    'ifsc_code',
    'branch',
    'opening_balance',
    'opening_date',
    'current_balance',
    'color',
    'is_active',
    'notes',
])]
class BankAccount extends Model
{
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'opening_balance' => 'decimal:2',
            'current_balance' => 'decimal:2',
            'opening_date' => 'date',
            'is_active' => 'boolean',
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
     * @return HasMany<BankTransaction, $this>
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class)->orderBy('date', 'desc')->orderBy('id', 'desc');
    }

    /**
     * Recalculate and update the current balance based on all credits and debits.
     */
    public function recalculateBalance(): float
    {
        $credits = (float) $this->transactions()->where('type', 'credit')->sum('amount');
        $debits = (float) $this->transactions()->where('type', 'debit')->sum('amount');
        $newBalance = (float) $this->opening_balance + $credits - $debits;

        $this->updateQuietly(['current_balance' => $newBalance]);

        return $newBalance;
    }

    /**
     * Return masked account number for privacy/display.
     */
    public function maskedAccountNumber(): string
    {
        if (blank($this->account_number)) {
            return '—';
        }

        $clean = trim((string) $this->account_number);
        if (Str::length($clean) <= 4) {
            return $clean;
        }

        return '•••• ' . Str::substr($clean, -4);
    }
}
