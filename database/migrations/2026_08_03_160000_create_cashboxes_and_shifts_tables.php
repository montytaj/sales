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
        Schema::create('cashboxes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('current_balance', 15, 2)->default(0.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('cashbox_user', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbox_id')->constrained('cashboxes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['cashbox_id', 'user_id']);
        });

        Schema::create('cashbox_shifts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cashbox_id')->constrained('cashboxes')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamp('opened_at');
            $table->timestamp('closed_at')->nullable();
            $table->decimal('opening_balance', 15, 2)->default(0.00);
            $table->decimal('expected_closing_balance', 15, 2)->default(0.00);
            $table->decimal('actual_closing_balance', 15, 2)->nullable();
            $table->decimal('difference_amount', 15, 2)->default(0.00);
            $table->enum('status', ['open', 'closed'])->default('open');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['cashbox_id', 'user_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashbox_shifts');
        Schema::dropIfExists('cashbox_user');
        Schema::dropIfExists('cashboxes');
    }
};
