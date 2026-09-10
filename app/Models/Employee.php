<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\EmployeeFactory;
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
 * @property int|null $employee_user_id
 * @property string $name
 * @property string|null $phone
 * @property string $default_daily_rate
 * @property string $advance_balance
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property User|null $employeeUser
 * @property Collection<int, EmployeePayment> $payments
 */
#[Fillable(['user_id', 'employee_user_id', 'name', 'phone', 'default_daily_rate', 'advance_balance', 'status', 'notes'])]
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'default_daily_rate' => 'decimal:2',
            'advance_balance' => 'decimal:2',
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
    public function employeeUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'employee_user_id');
    }

    /**
     * @return HasMany<EmployeePayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(EmployeePayment::class);
    }

    /**
     * Recalculate and update the running advance balance for this employee.
     */
    public function recalculateAdvanceBalance(): void
    {
        $totalAdvanceGiven = (float) $this->payments()
            ->where('type', 'advance_given')
            ->sum('amount');

        $totalAdvanceRepaidOrDeducted = (float) $this->payments()
            ->whereIn('type', ['advance_repaid', 'salary_deduction'])
            ->sum('amount');

        $this->update([
            'advance_balance' => $totalAdvanceGiven - $totalAdvanceRepaidOrDeducted,
        ]);
    }
}
