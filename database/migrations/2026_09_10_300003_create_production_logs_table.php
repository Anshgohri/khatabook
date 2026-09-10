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
        Schema::create('production_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();
            $table->foreignId('finished_product_id')->constrained('products')->cascadeOnDelete();
            $table->integer('quantity_produced');
            $table->foreignId('raw_material_id')->nullable()->constrained('products')->nullOnDelete();
            $table->integer('raw_material_consumed_qty')->default(0);
            $table->decimal('worker_wage', 10, 2)->default(0.00);
            $table->date('date');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['finished_product_id', 'date']);
            $table->index(['user_id', 'date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('production_logs');
    }
};
