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
        Schema::table('cheques', function (Blueprint $table) {
            if (!Schema::hasColumn('cheques', 'type')) {
                $table->enum('type', ['incoming', 'outgoing'])->default('incoming')->after('status');
            }
            if (!Schema::hasColumn('cheques', 'cashbox_id')) {
                $table->foreignId('cashbox_id')->nullable()->after('type')->constrained('cashboxes')->nullOnDelete();
            }
            if (!Schema::hasColumn('cheques', 'account_id')) {
                $table->foreignId('account_id')->nullable()->after('cashbox_id')->constrained('accounts')->nullOnDelete();
            }
            if (!Schema::hasColumn('cheques', 'journal_entry_id')) {
                $table->foreignId('journal_entry_id')->nullable()->after('account_id')->constrained('journal_entries')->nullOnDelete();
            }
            if (!Schema::hasColumn('cheques', 'cleared_at')) {
                $table->timestamp('cleared_at')->nullable()->after('due_date');
            }
            if (!Schema::hasColumn('cheques', 'payee_name')) {
                $table->string('payee_name')->nullable()->after('drawer_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cheques', function (Blueprint $table) {
            $table->dropForeign(['cashbox_id']);
            $table->dropForeign(['account_id']);
            $table->dropForeign(['journal_entry_id']);
            $table->dropColumn(['type', 'cashbox_id', 'account_id', 'journal_entry_id', 'cleared_at', 'payee_name']);
        });
    }
};
