<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\ProductionLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $employee_id
 * @property int|null $finished_product_id
 * @property int $quantity_produced
 * @property int|null $raw_material_id
 * @property int $raw_material_consumed_qty
 * @property string $worker_wage
 * @property Carbon $date
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property User $user
 * @property Employee|null $employee
 * @property Product|null $finishedProduct
 * @property Product|null $rawMaterial
 * @property Collection<int, ProductionLogItem> $items
 */
#[Fillable(['user_id', 'employee_id', 'finished_product_id', 'quantity_produced', 'raw_material_id', 'raw_material_consumed_qty', 'worker_wage', 'date', 'notes'])]
class ProductionLog extends Model
{
    /** @use HasFactory<ProductionLogFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'date' => 'date',
            'quantity_produced' => 'integer',
            'raw_material_consumed_qty' => 'integer',
            'worker_wage' => 'decimal:2',
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
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function finishedProduct(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'finished_product_id');
    }

    /**
     * @return BelongsTo<Product, $this>
     */
    public function rawMaterial(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'raw_material_id');
    }

    /**
     * @return HasMany<ProductionLogItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(ProductionLogItem::class);
    }

    /**
     * Sync items and adjust inventory stock levels & employee wage payment.
     *
     * @param  array<int, array{finished_product_id: int, quantity_produced: int, raw_material_id: ?int, raw_quantity_consumed: int}>  $itemsData
     */
    public function syncItemsAndInventory(array $itemsData): void
    {
        $this->load('items');

        // 1. Revert previous inventory stock adjustments for existing items
        foreach ($this->items as $existingItem) {
            if ($existingItem->finished_product_id && $existingItem->quantity_produced > 0) {
                Product::where('id', $existingItem->finished_product_id)->decrement('stock_level', $existingItem->quantity_produced);
            }
            if ($existingItem->raw_material_id && $existingItem->raw_material_consumed_qty > 0) {
                Product::where('id', $existingItem->raw_material_id)->increment('stock_level', $existingItem->raw_material_consumed_qty);
            }
        }

        // 2. Delete existing items
        $this->items()->delete();

        // 3. Create new items and apply stock adjustments
        $totalQty = 0;
        $totalRawQty = 0;
        $firstFinishedProductId = null;
        $firstRawMaterialId = null;

        foreach ($itemsData as $data) {
            $finishedProductId = (int) $data['finished_product_id'];
            $qtyProduced = max(1, (int) ($data['quantity_produced'] ?? 1));
            $rawMaterialId = ! empty($data['raw_material_id']) ? (int) $data['raw_material_id'] : null;
            $rawQtyConsumed = max(0, (int) ($data['raw_quantity_consumed'] ?? 0));

            $this->items()->create([
                'finished_product_id' => $finishedProductId,
                'quantity_produced' => $qtyProduced,
                'raw_material_id' => $rawMaterialId,
                'raw_material_consumed_qty' => $rawQtyConsumed,
            ]);

            // Apply stock changes
            Product::where('id', $finishedProductId)->increment('stock_level', $qtyProduced);
            if ($rawMaterialId && $rawQtyConsumed > 0) {
                Product::where('id', $rawMaterialId)->decrement('stock_level', $rawQtyConsumed);
            }

            $totalQty += $qtyProduced;
            $totalRawQty += $rawQtyConsumed;
            if (! $firstFinishedProductId) {
                $firstFinishedProductId = $finishedProductId;
            }
            if (! $firstRawMaterialId && $rawMaterialId) {
                $firstRawMaterialId = $rawMaterialId;
            }
        }

        // Update main table legacy columns
        $this->updateQuietly([
            'finished_product_id' => $firstFinishedProductId ?? $this->finished_product_id,
            'quantity_produced' => $totalQty > 0 ? $totalQty : $this->quantity_produced,
            'raw_material_id' => $firstRawMaterialId ?? $this->raw_material_id,
            'raw_material_consumed_qty' => $totalRawQty,
        ]);

        $this->syncEmployeePayment();
    }

    public function syncEmployeePayment(): void
    {
        $existingPayment = EmployeePayment::query()
            ->where('employee_id', $this->employee_id)
            ->where('type', 'daily_pay')
            ->whereDate('date', $this->date)
            ->first();

        if ($this->employee_id && (float) $this->worker_wage > 0) {
            $notes = __('Production wage for daily log #:id', ['id' => $this->id]);

            if ($existingPayment) {
                $existingPayment->update([
                    'amount' => $this->worker_wage,
                    'date' => $this->date,
                    'notes' => $notes,
                ]);
            } else {
                EmployeePayment::create([
                    'employee_id' => $this->employee_id,
                    'user_id' => $this->user_id,
                    'date' => $this->date,
                    'type' => 'daily_pay',
                    'amount' => $this->worker_wage,
                    'payment_method' => 'cash',
                    'notes' => $notes,
                ]);
            }
        } elseif ($existingPayment) {
            $existingPayment->delete();
        }
    }

    protected static function booted(): void
    {
        static::created(function (self $log): void {
            if ($log->finished_product_id && $log->items()->count() === 0) {
                $log->syncItemsAndInventory([
                    [
                        'finished_product_id' => $log->finished_product_id,
                        'quantity_produced' => $log->quantity_produced,
                        'raw_material_id' => $log->raw_material_id,
                        'raw_quantity_consumed' => $log->raw_material_consumed_qty,
                    ],
                ]);
            }
        });

        static::deleting(function (self $log): void {
            // Revert stock adjustments for all items
            foreach ($log->items as $item) {
                if ($item->finished_product_id && $item->quantity_produced > 0) {
                    Product::where('id', $item->finished_product_id)->decrement('stock_level', $item->quantity_produced);
                }
                if ($item->raw_material_id && $item->raw_material_consumed_qty > 0) {
                    Product::where('id', $item->raw_material_id)->increment('stock_level', $item->raw_material_consumed_qty);
                }
            }

            // Fallback for legacy items without production_log_items
            if ($log->items->isEmpty()) {
                if ($log->finishedProduct) {
                    $log->finishedProduct->decrement('stock_level', $log->quantity_produced);
                }
                if ($log->rawMaterial && $log->raw_material_consumed_qty > 0) {
                    $log->rawMaterial->increment('stock_level', $log->raw_material_consumed_qty);
                }
            }

            // Remove worker payment
            if ($log->employee_id && (float) $log->worker_wage > 0) {
                EmployeePayment::query()
                    ->where('employee_id', $log->employee_id)
                    ->where('type', 'daily_pay')
                    ->whereDate('date', $log->date)
                    ->delete();
            }
        });
    }
}
