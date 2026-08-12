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
            $tables = ['purchase_invoices', 'invoices'];
            foreach ($tables as $table) {
                if (Schema::hasTable($table) && Schema::hasColumn($table, 'status')) {
                    DB::statement("ALTER TABLE `{$table}` MODIFY COLUMN `status` VARCHAR(50) NOT NULL DEFAULT 'draft'");
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
