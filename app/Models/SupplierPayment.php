<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\SupplierPaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $supplier_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $type
 * @property string $amount
 * @property string $payment_method
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Supplier $supplier
 * @property User $user
 */
#[Fillable(['supplier_id', 'user_id', 'date', 'type', 'amount', 'payment_method', 'notes', 'bill_path'])]
class SupplierPayment extends Model
{
    /** @use HasFactory<SupplierPaymentFactory> */
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
     * @return BelongsTo<Supplier, $this>
     */
    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
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
            $payment->supplier->recalculateOutstandingBalance();
        });

        static::deleted(function (self $payment): void {
            $payment->supplier->recalculateOutstandingBalance();
        });
    }
}
