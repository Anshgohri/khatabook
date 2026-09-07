<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\InventoryLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['product_id', 'quantity_change', 'transaction_type', 'date', 'notes'])]
class InventoryLog extends Model
{
    /** @use HasFactory<InventoryLogFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity_change' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    protected static function booted(): void
    {
        static::created(function (self $log): void {
            $log->product()->increment('stock_level', $log->quantity_change);
        });

        static::deleted(function (self $log): void {
            $log->product()->decrement('stock_level', $log->quantity_change);
        });
    }
}
