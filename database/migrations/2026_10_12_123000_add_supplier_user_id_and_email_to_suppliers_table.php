<?php

use App\Enums\RoleName;
use App\Models\Role;
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
        Schema::table('suppliers', function (Blueprint $table) {
            $table->foreignId('supplier_user_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            $table->string('email')->nullable()->after('name');
        });

        Role::firstOrCreate(
            ['name' => RoleName::Supplier->value],
            ['description' => 'Supplier account for raw material vendors and purchases.']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('suppliers', function (Blueprint $table) {
            $table->dropForeign(['supplier_user_id']);
            $table->dropColumn(['supplier_user_id', 'email']);
        });
    }
};
