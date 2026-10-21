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
        Schema::table('employees', function (Blueprint $table) {
            $table->decimal('ghodi_medium_rate', 8, 2)->default(11.00)->after('default_daily_rate');
            $table->decimal('ghodi_heavy_rate', 8, 2)->default(12.00)->after('ghodi_medium_rate');
            $table->decimal('siddi_danda_rate', 8, 2)->default(4.00)->after('ghodi_heavy_rate');
            $table->decimal('challi_rate', 8, 2)->default(20.00)->after('siddi_danda_rate');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn([
                'ghodi_medium_rate',
                'ghodi_heavy_rate',
                'siddi_danda_rate',
                'challi_rate',
            ]);
        });
    }
};
