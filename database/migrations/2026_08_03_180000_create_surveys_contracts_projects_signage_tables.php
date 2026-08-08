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
        // 1. Site Surveys
        Schema::create('site_surveys', function (Blueprint $table) {
            $table->id();
            $table->string('survey_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->string('site_address');
            $table->string('location_coordinates')->nullable();
            $table->text('dimensions_data')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->date('survey_date');
            $table->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Contracts
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->text('scope_of_work');
            $table->decimal('total_amount', 15, 2)->default(0.00);
            $table->decimal('discount_amount', 15, 2)->default(0.00);
            $table->decimal('tax_amount', 15, 2)->default(0.00);
            $table->decimal('net_amount', 15, 2)->default(0.00);
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->text('warranty_terms')->nullable();
            $table->text('payment_terms')->nullable();
            $table->enum('status', ['draft', 'pending_approval', 'approved', 'active', 'completed', 'cancelled'])->default('draft');
            $table->boolean('is_approved')->default(false);
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Contract Payment Terms / Milestones Schedule
        Schema::create('contract_payment_terms', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained('contracts')->cascadeOnDelete();
            $table->string('milestone_name');
            $table->date('due_date');
            $table->enum('amount_type', ['fixed', 'percentage'])->default('fixed');
            $table->decimal('value', 15, 2);
            $table->decimal('calculated_amount', 15, 2);
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->enum('status', ['pending', 'due', 'overdue', 'paid'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 4. Projects
        Schema::create('projects', function (Blueprint $table) {
            $table->id();
            $table->string('project_number', 50)->unique();
            $table->string('name');
            $table->foreignId('contract_id')->nullable()->constrained('contracts')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->date('start_date');
            $table->date('expected_end_date')->nullable();
            $table->date('actual_end_date')->nullable();
            $table->decimal('budget', 15, 2)->default(0.00);
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->enum('status', ['planning', 'in_progress', 'on_hold', 'substantially_completed', 'finally_completed', 'cancelled'])->default('planning');
            $table->date('warranty_start_date')->nullable();
            $table->date('warranty_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 5. Project Stages / Phases
        Schema::create('project_stages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('name');
            $table->decimal('weight_percentage', 5, 2)->default(0.00);
            $table->decimal('completion_percentage', 5, 2)->default(0.00);
            $table->date('start_date')->nullable();
            $table->date('due_date')->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamps();
        });

        // 6. Project Change Orders
        Schema::create('project_change_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('order_number', 50)->unique();
            $table->text('description');
            $table->decimal('cost_impact', 15, 2)->default(0.00);
            $table->integer('time_impact_days')->default(0);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });

        // 7. Project Expenses / Subcontractors
        Schema::create('project_expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->enum('type', ['material', 'subcontractor', 'labor', 'other'])->default('material');
            $table->string('description');
            $table->decimal('amount', 15, 2)->default(0.00);
            $table->date('expense_date');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 8. Signage Orders
        Schema::create('signage_orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number', 50)->unique();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $table->string('dimensions', 100)->nullable();
            $table->string('design_file_path')->nullable();
            $table->boolean('design_approved')->default(false);
            $table->timestamp('design_approved_at')->nullable();
            $table->foreignId('design_approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('manufacturing_status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->enum('installation_status', ['pending', 'scheduled', 'installed'])->default('pending');
            $table->date('installation_date')->nullable();
            $table->string('installer_name')->nullable();
            $table->unsignedInteger('warranty_months')->default(12);
            $table->text('maintenance_notes')->nullable();
            $table->enum('status', ['new', 'design_phase', 'manufacturing', 'installation', 'completed', 'maintenance_due', 'cancelled'])->default('new');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('signage_orders');
        Schema::dropIfExists('project_expenses');
        Schema::dropIfExists('project_change_orders');
        Schema::dropIfExists('project_stages');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('contract_payment_terms');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('site_surveys');
    }
};
