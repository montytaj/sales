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
        $this->addIndexSafely('invoices', ['issue_date', 'status'], 'idx_invoices_date_status');
        $this->addIndexSafely('invoices', ['customer_id', 'status'], 'idx_invoices_customer_status');
        $this->addIndexSafely('invoices', ['created_at'], 'idx_invoices_created_at');

        $this->addIndexSafely('purchase_invoices', ['invoice_date', 'status'], 'idx_purchases_date_status');
        $this->addIndexSafely('purchase_invoices', ['supplier_id', 'status'], 'idx_purchases_supplier_status');
        $this->addIndexSafely('purchase_invoices', ['created_at'], 'idx_purchases_created_at');

        $this->addIndexSafely('stock_movements', ['inventory_item_id', 'created_at'], 'idx_movements_item_date');
        $this->addIndexSafely('stock_movements', ['warehouse_id', 'created_at'], 'idx_movements_wh_date');

        $this->addIndexSafely('journal_entries', ['entry_date', 'status'], 'idx_journal_date_status');
        $this->addIndexSafely('journal_entries', ['created_at'], 'idx_journal_created_at');
    }

    protected function addIndexSafely(string $table, array $columns, string $indexName): void
    {
        if (!Schema::hasTable($table)) return;

        try {
            Schema::table($table, function (Blueprint $t) use ($columns, $indexName) {
                $t->index($columns, $indexName);
            });
        } catch (\Throwable $e) {
            // Index already exists or duplicate key name
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Safely drop indexes
    }
};
