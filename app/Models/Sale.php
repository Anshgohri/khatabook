<?php

namespace App\Models;

use App\Concerns\Auditable;
use App\Concerns\NotifiesHighValueTransactions;
use Database\Factories\SaleFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'customer_id', 'date', 'customer_name', 'items_sold', 'quantity', 'unit_price', 'total_amount', 'discount', 'payment_status', 'notes'])]
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
            'discount' => 'decimal:2',
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
    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }

    /**
     * Sync sale items and update inventory stock logs.
     *
     * @param  array<int, array{product_id: ?int, quantity: int, unit_price: float}>  $itemsData
     */
    public function syncItemsAndInventory(array $itemsData): void
    {
        // 1. Revert previous inventory logs for this sale
        $this->clearInventoryLogs();

        // 2. Delete existing items
        $this->items()->delete();

        if (empty($itemsData)) {
            return;
        }

        $totalAmount = 0.0;
        $totalQty = 0;
        $itemDescriptions = [];

        foreach ($itemsData as $data) {
            $productId = ! empty($data['product_id']) ? (int) $data['product_id'] : null;
            $qty = max(1, (int) ($data['quantity'] ?? 1));
            $unitPrice = (float) ($data['unit_price'] ?? 0);
            $totalPrice = $qty * $unitPrice;

            $totalAmount += $totalPrice;
            $totalQty += $qty;

            $product = $productId ? Product::find($productId) : null;
            $productName = $product ? $product->name : ($data['product_name'] ?? 'Item');

            $itemDescriptions[] = "{$qty}x {$productName}";

            $this->items()->create([
                'product_id' => $productId,
                'quantity' => $qty,
                'unit_price' => $unitPrice,
                'total_price' => $totalPrice,
            ]);

            // Deduct stock via InventoryLog if linked to a product
            if ($productId && $product) {
                InventoryLog::create([
                    'product_id' => $productId,
                    'quantity_change' => -$qty,
                    'transaction_type' => 'sale',
                    'date' => $this->date ? $this->date->toDateString() : now()->toDateString(),
                    'notes' => "Sale #{$this->id}: {$productName}",
                ]);
            }
        }

        $summaryText = implode(', ', $itemDescriptions);
        $discount = min($totalAmount, max(0, (float) ($this->discount ?? 0)));
        $finalTotal = max(0, $totalAmount - $discount);

        $this->updateQuietly([
            'items_sold' => $summaryText ?: $this->items_sold,
            'quantity' => $totalQty > 0 ? $totalQty : $this->quantity,
            'unit_price' => ($totalQty > 0 && count($itemsData) === 1) ? $itemsData[0]['unit_price'] : ($totalQty > 0 ? $totalAmount / $totalQty : $this->unit_price),
            'total_amount' => $finalTotal,
        ]);
    }

    public function clearInventoryLogs(): void
    {
        $logs = InventoryLog::query()
            ->where('transaction_type', 'sale')
            ->where('notes', 'like', "Sale #{$this->id}:%")
            ->get();

        foreach ($logs as $log) {
            $log->delete();
        }
    }

    protected static function booted(): void
    {
        static::saving(function (self $sale): void {
            if (! $sale->isDirty('total_amount') && $sale->quantity && $sale->unit_price) {
                $sale->total_amount = $sale->quantity * $sale->unit_price;
            }
        });

        static::created(function (self $sale): void {
            static::notifyIfHighValue('sale', $sale, (float) $sale->total_amount);
        });

        static::deleting(function (self $sale): void {
            $sale->clearInventoryLogs();
        });
    }
}
