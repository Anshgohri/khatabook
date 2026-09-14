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
        Schema::table('financiers', function (Blueprint $table) {
            $table->string('interest_type')->default('interest_only')->after('payout_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('financiers', function (Blueprint $table) {
            $table->dropColumn('interest_type');
        });
    }
};
