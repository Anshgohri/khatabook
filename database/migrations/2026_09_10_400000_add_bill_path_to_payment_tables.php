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
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->string('bill_path')->nullable()->after('notes');
        });

        Schema::table('financier_payments', function (Blueprint $table) {
            $table->string('bill_path')->nullable()->after('notes');
        });

        Schema::table('employee_payments', function (Blueprint $table) {
            $table->string('bill_path')->nullable()->after('notes');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->string('bill_path')->nullable()->after('notes');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('supplier_payments', function (Blueprint $table) {
            $table->dropColumn('bill_path');
        });

        Schema::table('financier_payments', function (Blueprint $table) {
            $table->dropColumn('bill_path');
        });

        Schema::table('employee_payments', function (Blueprint $table) {
            $table->dropColumn('bill_path');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropColumn('bill_path');
        });
    }
};
