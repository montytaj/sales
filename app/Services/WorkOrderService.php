<?php

namespace App\Services;

use App\Models\WorkOrder;
use App\Models\WorkOrderAuthorization;
use App\Models\WorkOrderTimeLog;
use App\Models\PaymentVoucher;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class WorkOrderService
{
    /**
     * Issue start authorization for a work order.
     */
    public function authorizeStart(WorkOrder $workOrder, bool $isOverride = false, ?string $overrideReason = null, ?string $notes = null): WorkOrderAuthorization
    {
        return DB::transaction(function () use ($workOrder, $isOverride, $overrideReason, $notes) {
            if ($workOrder->authorization) {
                return $workOrder->authorization;
            }

            $paidAmount = 0.0;
            $remainingBalance = 0.0;

            if ($workOrder->invoice) {
                $paidAmount = (float) PaymentVoucher::where('invoice_id', $workOrder->invoice_id)
                    ->where('status', 'completed')
                    ->sum('amount');

                $remainingBalance = max(0, $workOrder->invoice->total_amount - $paidAmount);
            }

            if ($isOverride && empty($overrideReason)) {
                throw new InvalidArgumentException("يجب كتابة سبب التجاوز الاستثنائي للتصريح.");
            }

            $authorizerId = auth()->id() ?? User::first()?->id ?? 1;

            $authorization = WorkOrderAuthorization::create([
                'work_order_id' => $workOrder->id,
                'authorized_by' => $authorizerId,
                'authorized_at' => now(),
                'paid_amount' => $paidAmount,
                'remaining_balance' => $remainingBalance,
                'is_override' => $isOverride,
                'override_reason' => $overrideReason,
                'notes' => $notes,
            ]);

            $workOrder->update(['status' => 'authorized_to_start']);

            ActivityLog::log(
                'work_order_authorized',
                $workOrder,
                $isOverride ? "Issued exceptional start override for work order {$workOrder->work_order_number}: {$overrideReason}" : "Issued start authorization for work order {$workOrder->work_order_number}"
            );

            return $authorization;
        });
    }

    /**
     * Start execution of work order by an operator.
     */
    public function startExecution(WorkOrder $workOrder, ?string $notes = null): void
    {
        DB::transaction(function () use ($workOrder, $notes) {
            if (!$workOrder->authorization) {
                throw new InvalidArgumentException("لا يمكن بدء العمل على هذا الأمر دون تصريح بدء معتمد من الإدارة.");
            }

            $workOrder->update(['status' => 'in_progress']);

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            WorkOrderTimeLog::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $userId,
                'action' => 'start',
                'logged_at' => now(),
                'notes' => $notes,
            ]);

            ActivityLog::log(
                'work_order_execution_started',
                $workOrder,
                "Operator started work order {$workOrder->work_order_number}"
            );
        });
    }

    /**
     * Pause execution of work order.
     */
    public function pauseExecution(WorkOrder $workOrder, ?string $notes = null): void
    {
        DB::transaction(function () use ($workOrder, $notes) {
            $workOrder->update(['status' => 'paused']);

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            WorkOrderTimeLog::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $userId,
                'action' => 'pause',
                'logged_at' => now(),
                'notes' => $notes,
            ]);

            ActivityLog::log(
                'work_order_execution_paused',
                $workOrder,
                "Operator paused work order {$workOrder->work_order_number}"
            );
        });
    }

    /**
     * Resume execution of work order.
     */
    public function resumeExecution(WorkOrder $workOrder, ?string $notes = null): void
    {
        DB::transaction(function () use ($workOrder, $notes) {
            $workOrder->update(['status' => 'in_progress']);

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            WorkOrderTimeLog::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $userId,
                'action' => 'resume',
                'logged_at' => now(),
                'notes' => $notes,
            ]);

            ActivityLog::log(
                'work_order_execution_resumed',
                $workOrder,
                "Operator resumed work order {$workOrder->work_order_number}"
            );
        });
    }

    /**
     * Complete execution of work order & record good/waste pieces.
     */
    public function completeExecution(WorkOrder $workOrder, int $goodPieces = 0, int $wastePieces = 0, ?string $notes = null): void
    {
        DB::transaction(function () use ($workOrder, $goodPieces, $wastePieces, $notes) {
            $workOrder->update([
                'status' => 'ready_for_delivery',
                'good_pieces' => $goodPieces,
                'waste_pieces' => $wastePieces,
            ]);

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            WorkOrderTimeLog::create([
                'work_order_id' => $workOrder->id,
                'user_id' => $userId,
                'action' => 'complete',
                'logged_at' => now(),
                'notes' => $notes,
            ]);

            ActivityLog::log(
                'work_order_completed',
                $workOrder,
                "Completed work order {$workOrder->work_order_number}. Good: {$goodPieces}, Waste: {$wastePieces}"
            );
        });
    }

    /**
     * Register delivery of work order.
     */
    public function deliverWorkOrder(WorkOrder $workOrder, string $receiverName, ?string $deliveryNotes = null): void
    {
        DB::transaction(function () use ($workOrder, $receiverName, $deliveryNotes) {
            $allowDeliveryWithBalance = setting('allow_delivery_with_balance', false);

            if (!$allowDeliveryWithBalance && $workOrder->invoice) {
                $paidAmount = (float) PaymentVoucher::where('invoice_id', $workOrder->invoice_id)
                    ->where('status', 'completed')
                    ->sum('amount');

                $remainingBalance = max(0, $workOrder->invoice->total_amount - $paidAmount);

                if ($remainingBalance > 0) {
                    throw new InvalidArgumentException("يمنع تسليم أمر العمل لوجود رصيد متبقي على العميل بقيمة ({$remainingBalance} SAR) حسب إعدادات النظام.");
                }
            }

            $workOrder->update([
                'status' => 'delivered',
                'delivery_receiver_name' => $receiverName,
                'delivery_notes' => $deliveryNotes,
                'delivered_at' => now(),
            ]);

            ActivityLog::log(
                'work_order_delivered',
                $workOrder,
                "Delivered work order {$workOrder->work_order_number} to {$receiverName}"
            );
        });
    }
}
