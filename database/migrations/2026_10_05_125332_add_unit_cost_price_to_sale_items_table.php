<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->decimal('unit_cost_price', 12, 2)->nullable()->after('unit_price');
        });

        // Backfill existing records with current product cost_price
        Illuminate\Support\Facades\DB::statement('
            UPDATE sale_items 
            SET unit_cost_price = (
                SELECT cost_price 
                FROM products 
                WHERE products.id = sale_items.product_id
            )
            WHERE unit_cost_price IS NULL AND product_id IS NOT NULL
        ');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sale_items', function (Blueprint $table) {
            $table->dropColumn('unit_cost_price');
        });
    }
};
