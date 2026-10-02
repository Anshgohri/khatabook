<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('production_log_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('production_log_id')->constrained('production_logs')->cascadeOnDelete();
            $table->foreignId('finished_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_produced')->default(1);
            $table->foreignId('raw_material_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('raw_material_consumed_qty')->default(0);
            $table->timestamps();
        });

        Schema::table('production_logs', function (Blueprint $table) {
            $table->foreignId('finished_product_id')->nullable()->change();
            $table->integer('quantity_produced')->default(0)->change();
        });

        $existingLogs = DB::table('production_logs')->get();
        foreach ($existingLogs as $log) {
            if (! empty($log->finished_product_id)) {
                DB::table('production_log_items')->insert([
                    'production_log_id' => $log->id,
                    'finished_product_id' => $log->finished_product_id,
                    'quantity_produced' => $log->quantity_produced ?? 1,
                    'raw_material_id' => $log->raw_material_id,
                    'raw_material_consumed_qty' => $log->raw_material_consumed_qty ?? 0,
                    'created_at' => $log->created_at ?? now(),
                    'updated_at' => $log->updated_at ?? now(),
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_log_items');

        Schema::table('production_logs', function (Blueprint $table) {
            $table->foreignId('finished_product_id')->nullable(false)->change();
            $table->integer('quantity_produced')->default(1)->change();
        });
    }
};
