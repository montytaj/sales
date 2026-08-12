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
        if (!Schema::hasColumn('cashboxes', 'account_id')) {
            Schema::table('cashboxes', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('branch_id')->constrained('accounts')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('customers', 'account_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('branch_id')->constrained('accounts')->nullOnDelete();
            });
        }

        if (!Schema::hasColumn('suppliers', 'account_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->foreignId('account_id')->nullable()->after('branch_id')->constrained('accounts')->nullOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('cashboxes', 'account_id')) {
            Schema::table('cashboxes', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
        }

        if (Schema::hasColumn('customers', 'account_id')) {
            Schema::table('customers', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
        }

        if (Schema::hasColumn('suppliers', 'account_id')) {
            Schema::table('suppliers', function (Blueprint $table) {
                $table->dropForeign(['account_id']);
                $table->dropColumn('account_id');
            });
        }
    }
};
