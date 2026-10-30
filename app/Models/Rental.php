<?php

namespace App\Models;

use App\Models\InventoryLog;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Rental extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'customer_name',
        'customer_phone',
        'customer_address',
        'rented_at',
        'expected_return_date',
        'returned_at',
        'security_deposit',
        'discount',
        'total_rent',
        'status',
        'settlement_type',
        'settlement_amount',
        'payment_method',
        'return_notes',
        'restock_inventory',
        'notes',
        'user_id',
    ];

    protected $casts = [
        'rented_at' => 'date',
        'expected_return_date' => 'date',
        'returned_at' => 'date',
        'security_deposit' => 'decimal:2',
        'discount' => 'decimal:2',
        'total_rent' => 'decimal:2',
        'settlement_amount' => 'decimal:2',
        'restock_inventory' => 'boolean',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(RentalItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Calculate days rented between rented_at and return/target date.
     */
    public function daysRented(?Carbon $until = null): int
    {
        if (! $this->rented_at) {
            return 1;
        }

        $start = Carbon::parse($this->rented_at)->startOfDay();
        $target = $until ? Carbon::parse($until)->startOfDay() : ($this->returned_at ? Carbon::parse($this->returned_at)->startOfDay() : now()->startOfDay());

        $days = (int) $start->diffInDays($target);

        return max(1, $days);
    }

    /**
     * Sum of daily rent across all items.
     */
    public function dailyRateTotal(): float
    {
        $this->loadMissing('items');

        return (float) $this->items->sum(function ($item) {
            return ((int) $item->quantity) * ((float) $item->daily_rent);
        });
    }

    /**
     * Calculate live rent based on days and discount.
     */
    public function calculateLiveRent(?Carbon $until = null, ?float $customDiscount = null): float
    {
        $days = $this->daysRented($until);
        $dailyTotal = $this->dailyRateTotal();
        $discount = $customDiscount !== null ? max(0, $customDiscount) : (float) $this->discount;

        $subtotal = $dailyTotal * $days;

        return max(0, $subtotal - $discount);
    }

    /**
     * Calculate financial settlement details.
     * Compares security deposit against rent charged.
     */
    public function calculateSettlement(?float $rent = null): array
    {
        $totalRent = $rent !== null ? max(0, $rent) : (float) $this->total_rent;
        $deposit = (float) $this->security_deposit;

        $difference = $deposit - $totalRent;

        if ($difference > 0) {
            return [
                'type' => 'refund_to_customer',
                'amount' => round($difference, 2),
                'label' => 'Refund to Customer',
                'description' => "Store refunds balance security deposit of ₹".number_format($difference, 2)." to customer.",
            ];
        } elseif ($difference < 0) {
            $due = abs($difference);
            return [
                'type' => 'collect_from_customer',
                'amount' => round($due, 2),
                'label' => 'Collect from Customer',
                'description' => "Customer pays remaining rent balance of ₹".number_format($due, 2)." after adjusting security deposit.",
            ];
        } else {
            return [
                'type' => 'even',
                'amount' => 0.00,
                'label' => 'Fully Settled (Even)',
                'description' => "Total rent exactly matches security deposit (₹0.00 balance).",
            ];
        }
    }

    /**
     * Complete and settle a rental return transaction.
     */
    public function completeReturn(array $data): void
    {
        DB::transaction(function () use ($data) {
            $wasAlreadyReturned = $this->status === 'returned';
            $returnDate = $data['returned_at'] ?? now()->format('Y-m-d');
            $discount = isset($data['discount']) ? (float) $data['discount'] : (float) $this->discount;

            // Compute days & rent if not explicitly given
            $totalRent = isset($data['total_rent'])
                ? (float) $data['total_rent']
                : $this->calculateLiveRent(Carbon::parse($returnDate), $discount);

            $settlement = $this->calculateSettlement($totalRent);

            $this->update([
                'status' => 'returned',
                'returned_at' => $returnDate,
                'discount' => $discount,
                'total_rent' => $totalRent,
                'settlement_type' => $settlement['type'],
                'settlement_amount' => $settlement['amount'],
                'payment_method' => $data['payment_method'] ?? 'cash',
                'return_notes' => $data['return_notes'] ?? null,
                'restock_inventory' => $data['restock_inventory'] ?? true,
            ]);

            // Update returned quantities for line items
            $this->loadMissing(['items.product']);
            $itemsReturned = $data['items_returned'] ?? [];

            foreach ($this->items as $index => $item) {
                $returnedQty = isset($itemsReturned[$item->id])
                    ? max(0, (int) $itemsReturned[$item->id])
                    : (isset($itemsReturned[$index]) ? max(0, (int) $itemsReturned[$index]) : (int) $item->quantity);

                $item->update([
                    'returned_quantity' => $returnedQty,
                ]);

                // If restock inventory is enabled and not already returned, log inventory return
                if (! $wasAlreadyReturned && ($data['restock_inventory'] ?? true) && $item->product_id && $returnedQty > 0) {
                    InventoryLog::create([
                        'product_id' => $item->product_id,
                        'quantity_change' => $returnedQty,
                        'transaction_type' => 'return',
                        'date' => $returnDate,
                        'notes' => "Rental #{$this->id} Returned: {$item->product?->name} (Qty: {$returnedQty})",
                    ]);
                }
            }
        });
    }
}
