<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\ProductionLogFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $user_id
 * @property int|null $employee_id
 * @property int $finished_product_id
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
 * @property Product $finishedProduct
 * @property Product|null $rawMaterial
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

    protected static function booted(): void
    {
        static::created(function (self $log): void {
            // Increase finished product stock level
            if ($log->finishedProduct) {
                $log->finishedProduct->increment('stock_level', $log->quantity_produced);
            }

            // Decrease raw material stock level
            if ($log->rawMaterial && $log->raw_material_consumed_qty > 0) {
                $log->rawMaterial->decrement('stock_level', $log->raw_material_consumed_qty);
            }

            // Record worker wage in EmployeePayment ledger if applicable
            if ($log->employee_id && (float) $log->worker_wage > 0) {
                EmployeePayment::create([
                    'employee_id' => $log->employee_id,
                    'user_id' => $log->user_id,
                    'date' => $log->date,
                    'type' => 'daily_pay',
                    'amount' => $log->worker_wage,
                    'payment_method' => 'cash',
                    'notes' => __('Production wage for producing :qty units of :product', [
                        'qty' => $log->quantity_produced,
                        'product' => $log->finishedProduct?->name ?? 'item',
                    ]),
                ]);
            }
        });

        static::deleted(function (self $log): void {
            if ($log->finishedProduct) {
                $log->finishedProduct->decrement('stock_level', $log->quantity_produced);
            }

            if ($log->rawMaterial && $log->raw_material_consumed_qty > 0) {
                $log->rawMaterial->increment('stock_level', $log->raw_material_consumed_qty);
            }
        });
    }
}
