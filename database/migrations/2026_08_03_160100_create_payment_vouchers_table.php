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
        Schema::create('payment_vouchers', function (Blueprint $table) {
            $table->id();
            $table->string('voucher_number', 50)->unique();
            $table->enum('type', ['receipt', 'payment', 'transfer'])->default('receipt');
            $table->foreignId('customer_id')->nullable()->constrained('customers')->nullOnDelete();
            $table->foreignId('supplier_id')->nullable()->constrained('suppliers')->nullOnDelete();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('cashbox_id')->nullable()->constrained('cashboxes')->nullOnDelete();
            $table->foreignId('target_cashbox_id')->nullable()->constrained('cashboxes')->nullOnDelete();
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('payment_date');
            $table->text('notes')->nullable();
            $table->enum('status', ['completed', 'cancelled'])->default('completed');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'customer_id', 'invoice_id', 'status']);
        });

        Schema::create('payment_voucher_lines', function (Blueprint $table) {
            $table->id();
            $table->foreignId('payment_voucher_id')->constrained('payment_vouchers')->cascadeOnDelete();
            $table->enum('payment_method', ['cash', 'bank_transfer', 'card', 'cheque', 'credit', 'e_wallet', 'other'])->default('cash');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->string('reference_number')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_voucher_lines');
        Schema::dropIfExists('payment_vouchers');
    }
};
