<?php

namespace App\Models;

use App\Concerns\Auditable;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['product_category_id', 'type', 'name', 'unit_price', 'unit', 'description', 'stock_level'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use Auditable, HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'stock_level' => 'integer',
        ];
    }

    /**
     * Scope query to only include finished products (by category name or finished_good type).
     */
    public function scopeFinished(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('category', fn ($cat) => $cat->where('name', 'like', '%finished%'))
                ->orWhere(function ($sub) {
                    $sub->where('type', 'finished_good')
                        ->where(function ($c) {
                            $c->whereNull('product_category_id')
                                ->orWhereDoesntHave('category');
                        });
                });
        });
    }

    /**
     * Scope query to only include raw materials (by category name or raw_material type).
     */
    public function scopeRawMaterial(Builder $query): Builder
    {
        return $query->where(function ($q) {
            $q->whereHas('category', fn ($cat) => $cat->where('name', 'like', '%raw%'))
                ->orWhere(function ($sub) {
                    $sub->where('type', 'raw_material')
                        ->where(function ($c) {
                            $c->whereNull('product_category_id')
                                ->orWhereDoesntHave('category');
                        });
                });
        });
    }

    /**
     * @return BelongsTo<ProductCategory, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    /**
     * @return HasMany<InventoryLog, $this>
     */
    public function inventoryLogs(): HasMany
    {
        return $this->hasMany(InventoryLog::class);
    }

    /**
     * @return HasMany<ProductionLog, $this>
     */
    public function productionLogs(): HasMany
    {
        return $this->hasMany(ProductionLog::class, 'finished_product_id');
    }

    /**
     * @return HasMany<SaleItem, $this>
     */
    public function saleItems(): HasMany
    {
        return $this->hasMany(SaleItem::class);
    }
}
