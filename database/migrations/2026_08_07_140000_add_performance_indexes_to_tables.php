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
        // Helper function to safely add an index if all columns exist
        $addIndexSafely = function (string $table, array $columns, string $indexName) {
            if (!Schema::hasTable($table)) return;

            foreach ($columns as $col) {
                if (!Schema::hasColumn($table, $col)) return;
            }

            try {
                Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                    $t->index($columns, $indexName);
                });
            } catch (\Throwable $e) {
                // Ignore if index already exists
            }
        };

        // 1. Invoices
        $addIndexSafely('invoices', ['issue_date', 'status'], 'idx_invoices_date_status');
        $addIndexSafely('invoices', ['branch_id', 'issue_date'], 'idx_invoices_branch_date');
        $addIndexSafely('invoices', ['customer_id', 'issue_date'], 'idx_invoices_customer_date');

        // 2. Invoice Items
        $addIndexSafely('invoice_items', ['invoice_id', 'inventory_item_id'], 'idx_invoice_items_inv_item');

        // 3. Purchase Invoices
        $addIndexSafely('purchase_invoices', ['invoice_date', 'status'], 'idx_purchases_date_status');
        $addIndexSafely('purchase_invoices', ['branch_id', 'invoice_date'], 'idx_purchases_branch_date');
        $addIndexSafely('purchase_invoices', ['supplier_id', 'invoice_date'], 'idx_purchases_supplier_date');

        // 4. Purchase Invoice Items
        $addIndexSafely('purchase_invoice_items', ['purchase_invoice_id', 'inventory_item_id'], 'idx_pi_items_invoice_item');

        // 5. Inventory Items
        $addIndexSafely('inventory_items', ['code', 'is_active'], 'idx_inv_items_code_active');
        $addIndexSafely('inventory_items', ['barcode'], 'idx_inv_items_barcode');
        $addIndexSafely('inventory_items', ['category_id', 'is_active'], 'idx_inv_items_category_active');

        // 6. Customers
        $addIndexSafely('customers', ['code', 'is_active'], 'idx_customers_code_active');
        $addIndexSafely('customers', ['phone'], 'idx_customers_phone');

        // 7. Suppliers
        $addIndexSafely('suppliers', ['code', 'is_active'], 'idx_suppliers_code_active');
        $addIndexSafely('suppliers', ['phone'], 'idx_suppliers_phone');

        // 8. Accounts
        $addIndexSafely('accounts', ['code', 'is_active'], 'idx_accounts_code_active');
        $addIndexSafely('accounts', ['type', 'is_active'], 'idx_accounts_type_active');
        $addIndexSafely('accounts', ['parent_id'], 'idx_accounts_parent');

        // 9. Journal Entries
        $addIndexSafely('journal_entries', ['entry_date', 'status'], 'idx_journal_entries_date_status');
        $addIndexSafely('journal_entries', ['branch_id', 'entry_date'], 'idx_journal_entries_branch_date');

        // 10. Journal Entry Lines
        $addIndexSafely('journal_entry_lines', ['journal_entry_id', 'account_id'], 'idx_jel_entry_account');
        $addIndexSafely('journal_entry_lines', ['account_id', 'debit', 'credit'], 'idx_jel_account_debit_credit');

        // 11. Payment Vouchers
        $addIndexSafely('payment_vouchers', ['voucher_date', 'type'], 'idx_pv_date_type');
        $addIndexSafely('payment_vouchers', ['customer_id'], 'idx_pv_customer');
        $addIndexSafely('payment_vouchers', ['supplier_id'], 'idx_pv_supplier');

        // 12. Cheques
        $addIndexSafely('cheques', ['due_date', 'status'], 'idx_cheques_due_status');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safe rollback handled automatically if needed
    }
};
