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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'cash_amount')) {
                $table->decimal('cash_amount', 15, 2)->default(0.00)->after('payment_type');
            }
            if (!Schema::hasColumn('invoices', 'bank_amount')) {
                $table->decimal('bank_amount', 15, 2)->default(0.00)->after('cash_amount');
            }
            if (!Schema::hasColumn('invoices', 'due_amount')) {
                $table->decimal('due_amount', 15, 2)->default(0.00)->after('bank_amount');
            }
            if (!Schema::hasColumn('invoices', 'cash_account_id')) {
                $table->foreignId('cash_account_id')->nullable()->after('due_amount')->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('cash_account_id')->constrained('accounts')->nullOnDelete();
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'cash_amount')) {
                $table->decimal('cash_amount', 15, 2)->default(0.00)->after('payment_type');
            }
            if (!Schema::hasColumn('purchase_invoices', 'bank_amount')) {
                $table->decimal('bank_amount', 15, 2)->default(0.00)->after('cash_amount');
            }
            if (!Schema::hasColumn('purchase_invoices', 'due_amount')) {
                $table->decimal('due_amount', 15, 2)->default(0.00)->after('bank_amount');
            }
            if (!Schema::hasColumn('purchase_invoices', 'cash_account_id')) {
                $table->foreignId('cash_account_id')->nullable()->after('due_amount')->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'bank_account_id')) {
                $table->foreignId('bank_account_id')->nullable()->after('cash_account_id')->constrained('accounts')->nullOnDelete();
            }
        });

        Schema::table('payment_voucher_lines', function (Blueprint $table) {
            if (!Schema::hasColumn('payment_voucher_lines', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('payment_method')->constrained('accounts')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_voucher_lines', function (Blueprint $table) {
            if (Schema::hasColumn('payment_voucher_lines', 'account_id')) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            }
        });

        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (Schema::hasColumn('purchase_invoices', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }
            if (Schema::hasColumn('purchase_invoices', 'cash_account_id')) {
                $table->dropForeign(['cash_account_id']);
                $table->dropColumn('cash_account_id');
            }
            $table->dropColumn(['cash_amount', 'bank_amount', 'due_amount']);
        });

        Schema::table('invoices', function (Blueprint $table) {
            if (Schema::hasColumn('invoices', 'bank_account_id')) {
                $table->dropForeign(['bank_account_id']);
                $table->dropColumn('bank_account_id');
            }
            if (Schema::hasColumn('invoices', 'cash_account_id')) {
                $table->dropForeign(['cash_account_id']);
                $table->dropColumn('cash_account_id');
            }
            $table->dropColumn(['cash_amount', 'bank_amount', 'due_amount']);
        });
    }
};
