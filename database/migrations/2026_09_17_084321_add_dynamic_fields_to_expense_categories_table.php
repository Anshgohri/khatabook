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
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->string('description')->nullable()->after('name');
            $table->string('color')->default('emerald')->after('description');
            $table->string('icon')->default('📁')->after('color');
            $table->string('type')->default('business')->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expense_categories', function (Blueprint $table) {
            $table->dropColumn(['description', 'color', 'icon', 'type']);
        });
    }
};
