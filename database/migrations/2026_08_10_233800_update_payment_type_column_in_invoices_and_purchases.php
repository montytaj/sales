<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();
        if ($driver === 'mysql') {
            $tables = ['invoices', 'purchase_invoices', 'sales_returns', 'purchase_returns'];
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'payment_type')) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `payment_type` VARCHAR(50) NOT NULL DEFAULT 'cash'");
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Reverting not required
    }
};
