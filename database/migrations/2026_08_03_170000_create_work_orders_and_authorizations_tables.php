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
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->string('work_order_number', 50)->unique();
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->nullOnDelete();
            
            // CNC & Sheet Details
            $table->unsignedInteger('sheet_count')->default(1);
            $table->string('sheet_type', 100)->default('MDF');
            $table->string('dimensions', 100)->nullable(); // e.g. 122x244 cm
            $table->string('thickness', 50)->nullable(); // e.g. 18mm
            $table->enum('priority', ['low', 'normal', 'high', 'urgent'])->default('normal');
            $table->date('due_date')->nullable();
            
            // 12 Statuses
            $table->enum('status', [
                'new',
                'pending_payment',
                'downpayment_received',
                'authorized_to_start',
                'pending_execution',
                'in_progress',
                'paused',
                'needs_info',
                'completed',
                'ready_for_delivery',
                'delivered',
                'cancelled'
            ])->default('new');

            // Quality & Rework tracking
            $table->unsignedInteger('good_pieces')->default(0);
            $table->unsignedInteger('waste_pieces')->default(0);
            
            // Delivery details
            $table->string('delivery_receiver_name')->nullable();
            $table->text('delivery_notes')->nullable();
            $table->timestamp('delivered_at')->nullable();

            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['customer_id', 'status', 'priority']);
        });

        // Start Authorization Entity
        Schema::create('work_order_authorizations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('authorized_by')->constrained('users')->cascadeOnDelete();
            $table->timestamp('authorized_at');
            $table->decimal('paid_amount', 15, 2)->default(0.00);
            $table->decimal('remaining_balance', 15, 2)->default(0.00);
            $table->boolean('is_override')->default(false);
            $table->text('override_reason')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // Time tracking logs
        Schema::create('work_order_time_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained('work_orders')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->enum('action', ['start', 'pause', 'resume', 'complete']);
            $table->timestamp('logged_at');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('work_order_time_logs');
        Schema::dropIfExists('work_order_authorizations');
        Schema::dropIfExists('work_orders');
    }
};
