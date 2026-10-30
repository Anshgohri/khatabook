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
        Schema::table('rentals', function (Blueprint $table) {
            $table->string('settlement_type')->nullable()->after('status'); // 'refund_to_customer', 'collect_from_customer', 'even'
            $table->decimal('settlement_amount', 10, 2)->default(0)->after('settlement_type');
            $table->string('payment_method')->nullable()->default('cash')->after('settlement_amount');
            $table->text('return_notes')->nullable()->after('payment_method');
            $table->boolean('restock_inventory')->default(true)->after('return_notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn([
                'settlement_type',
                'settlement_amount',
                'payment_method',
                'return_notes',
                'restock_inventory',
            ]);
        });
    }
};
