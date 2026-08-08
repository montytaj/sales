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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->enum('type', ['individual', 'company'])->default('individual');
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('phone')->nullable();
            $table->string('phone_secondary')->nullable();
            $table->string('email')->nullable();
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('cr_number', 50)->nullable();
            $table->string('vat_number', 50)->nullable();
            $table->decimal('credit_limit', 15, 2)->default(0.00);
            $table->unsignedInteger('credit_period_days')->default(0);
            $table->enum('category', ['regular', 'vip', 'corporate', 'wholesale'])->default('regular');
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->timestamps();

            $table->index(['type', 'is_active', 'category']);
            $table->index('phone');
            $table->index('vat_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
