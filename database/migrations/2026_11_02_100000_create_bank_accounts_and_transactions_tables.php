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
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('bank_name'); // e.g. HDFC Bank, SBI, ICICI, Cash in Bank
            $table->string('account_name'); // e.g. Main Current Account, Reserve
            $table->string('account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('branch')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->date('opening_date')->nullable();
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->string('color', 30)->default('emerald');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('bank_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bank_account_id')->constrained('bank_accounts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('date');
            $table->enum('type', ['credit', 'debit']); // credit = money in, debit = money out
            $table->decimal('amount', 15, 2);
            $table->string('category')->default('General');
            $table->string('party_name')->nullable(); // payer or payee
            $table->string('reference_number')->nullable(); // UTR / Cheque / Ref ID
            $table->text('description')->nullable();
            $table->string('receipt_path')->nullable();
            $table->decimal('running_balance', 15, 2)->nullable();
            $table->timestamps();

            $table->index(['bank_account_id', 'date']);
            $table->index(['date', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transactions');
        Schema::dropIfExists('bank_accounts');
    }
};
