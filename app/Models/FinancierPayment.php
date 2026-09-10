<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\FinancierPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $financier_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $type
 * @property string $amount
 * @property string $payment_method
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Financier $financier
 * @property User $user
 */
#[Fillable(['financier_id', 'user_id', 'date', 'type', 'amount', 'payment_method', 'notes'])]
class FinancierPayment extends Model
{
    /** @use HasFactory<FinancierPaymentFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'amount' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Financier, $this>
     */
    public function financier(): BelongsTo
    {
        return $this->belongsTo(Financier::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    protected static function booted(): void
    {
        static::saved(function (self $payment): void {
            $payment->financier->recalculateOutstandingBalance();
        });

        static::deleted(function (self $payment): void {
            $payment->financier->recalculateOutstandingBalance();
        });
    }
}
