<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\EmployeePaymentFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $employee_id
 * @property int $user_id
 * @property Carbon $date
 * @property string $type
 * @property string $amount
 * @property string $payment_method
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Employee $employee
 * @property User $user
 */
#[Fillable(['employee_id', 'user_id', 'date', 'type', 'amount', 'payment_method', 'notes'])]
class EmployeePayment extends Model
{
    /** @use HasFactory<EmployeePaymentFactory> */
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
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
            $payment->employee->recalculateAdvanceBalance();
        });

        static::deleted(function (self $payment): void {
            $payment->employee->recalculateAdvanceBalance();
        });
    }
}
