<?php

namespace App\Models;

use Database\Factories\ProductionLogItemFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $production_log_id
 * @property int $finished_product_id
 * @property int $quantity_produced
 * @property int|null $raw_material_id
 * @property int $raw_material_consumed_qty
 * @property ProductionLog $productionLog
 * @property Product $finishedProduct
 * @property Product|null $rawMaterial
 */
#[Fillable(['production_log_id', 'finished_product_id', 'quantity_produced', 'raw_material_id', 'raw_material_consumed_qty'])]
class ProductionLogItem extends Model
{
    /** @use HasFactory<ProductionLogItemFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity_produced' => 'integer',
            'raw_material_consumed_qty' => 'integer',
        ];
    }

    /**
     * @return BelongsTo<ProductionLog, $this>
     */
    public function productionLog(): BelongsTo
    {
        return $this->belongsTo(ProductionLog::class);
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
}
