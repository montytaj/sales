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
        Schema::table('payment_vouchers', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_vouchers', 'purchase_invoice_id')) {
                $table->foreignId('purchase_invoice_id')->nullable()->after('invoice_id')->constrained('purchase_invoices')->nullOnDelete();
                $table->index(['supplier_id', 'purchase_invoice_id']);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_vouchers', function (Blueprint $table) {
            if (Schema::hasColumn('payment_vouchers', 'purchase_invoice_id')) {
                $table->dropForeign(['purchase_invoice_id']);
                $table->dropColumn('purchase_invoice_id');
            }
        });
    }
};
