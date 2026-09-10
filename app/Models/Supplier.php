<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\SupplierFactory;
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
 * @property string $name
 * @property string|null $phone
 * @property string|null $location
 * @property string|null $material_supplied
 * @property string $outstanding_balance
 * @property string $status
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property Collection<int, SupplierPayment> $payments
 */
#[Fillable(['user_id', 'name', 'phone', 'location', 'material_supplied', 'outstanding_balance', 'status', 'notes'])]
class Supplier extends Model
{
    /** @use HasFactory<SupplierFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
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
     * @return HasMany<SupplierPayment, $this>
     */
    public function payments(): HasMany
    {
        return $this->hasMany(SupplierPayment::class);
    }

    /**
     * Recalculate and update the running balance owed to this supplier.
     */
    public function recalculateOutstandingBalance(): void
    {
        $totalPurchases = (float) $this->payments()
            ->where('type', 'raw_material_purchase')
            ->sum('amount');

        $totalPaid = (float) $this->payments()
            ->where('type', 'payment_made')
            ->sum('amount');

        $this->update([
            'outstanding_balance' => $totalPurchases - $totalPaid,
        ]);
    }
}
