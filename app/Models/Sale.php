<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Concerns\NotifiesHighValueTransactions;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['user_id', 'date', 'customer_name', 'items_sold', 'quantity', 'unit_price', 'total_amount', 'payment_status', 'notes'])]
class Sale extends Model
{
    /** @use HasFactory<SaleFactory> */
    use Auditable, HasFactory, NotifiesHighValueTransactions;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
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
        static::saving(function (self $sale): void {
            if (! $sale->isDirty('total_amount')) {
                $sale->total_amount = $sale->quantity * $sale->unit_price;
            }
        });

        static::created(function (self $sale): void {
            static::notifyIfHighValue('sale', $sale, (float) $sale->total_amount);
        });
    }
}
