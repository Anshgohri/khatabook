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
        Schema::create('manual_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();

            // Party details
            $table->string('party_type')->default('other'); // supplier, financier, employee, other
            $table->string('party_name');
            $table->string('party_phone')->nullable();

            // Optional links to existing entities
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('financier_id')->nullable()->constrained('financiers')->nullOnDelete();
            $table->foreignId('employee_id')->nullable()->constrained('employees')->nullOnDelete();

            // Payment details
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->string('frequency')->default('daily'); // daily, weekly, monthly, one_time
            $table->string('priority')->default('normal'); // low, normal, high, urgent

            // Status & payment tracking
            $table->string('status')->default('pending'); // pending, paid, skipped, cancelled
            $table->decimal('paid_amount', 12, 2)->default(0);
            $table->date('paid_date')->nullable();
            $table->string('payment_method')->nullable(); // cash, upi, bank_transfer, cheque, other
            $table->foreignId('bank_account_id')->nullable()->constrained('bank_accounts')->nullOnDelete();

            $table->text('notes')->nullable();
            $table->boolean('is_recurring')->default(true);
            $table->timestamps();

            $table->index(['due_date', 'status']);
            $table->index(['party_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('manual_records');
    }
};
