<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Concerns\NotifiesHighValueTransactions;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'bank_account_id',
    'user_id',
    'date',
    'type',
    'amount',
    'category',
    'party_name',
    'reference_number',
    'description',
    'receipt_path',
    'running_balance',
])]
class BankTransaction extends Model
{
    use Auditable, HasFactory, NotifiesHighValueTransactions;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
            'running_balance' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<BankAccount, $this>
     */
    public function account(): BelongsTo
    {
        return $this->belongsTo(BankAccount::class, 'bank_account_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope query to credits only.
     *
     * @param  Builder<BankTransaction>  $query
     * @return Builder<BankTransaction>
     */
    public function scopeCredits(Builder $query): Builder
    {
        return $query->where('type', 'credit');
    }

    /**
     * Scope query to debits only.
     *
     * @param  Builder<BankTransaction>  $query
     * @return Builder<BankTransaction>
     */
    public function scopeDebits(Builder $query): Builder
    {
        return $query->where('type', 'debit');
    }

    /**
     * Return public URL for receipt if exists.
     */
    public function receiptUrl(): ?string
    {
        if (blank($this->receipt_path)) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_path);
    }

    protected static function booted(): void
    {
        static::saved(function (self $transaction): void {
            $transaction->account?->recalculateBalance();
        });

        static::deleted(function (self $transaction): void {
            $transaction->account?->recalculateBalance();
        });

        static::created(function (self $transaction): void {
            static::notifyIfHighValue('bank_transaction', $transaction, (float) $transaction->amount);
        });
    }
}
