<?php

namespace App\Services;

use App\Models\Contract;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Models\ProjectChangeOrder;
use App\Models\ProjectExpense;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\DB;

class ProjectService
{
    /**
     * Create project from approved contract.
     */
    public function createProjectFromContract(Contract $contract, ?int $managerId = null, ?string $notes = null): Project
    {
        return DB::transaction(function () use ($contract, $managerId, $notes) {
            $project = Project::create([
                'project_number' => Project::generateProjectNumber(),
                'name' => 'مشروع عقد رقم (' . $contract->contract_number . ') - ' . $contract->customer->name,
                'contract_id' => $contract->id,
                'customer_id' => $contract->customer_id,
                'branch_id' => $contract->branch_id,
                'manager_id' => $managerId ?? auth()->id(),
                'start_date' => $contract->start_date,
                'expected_end_date' => $contract->end_date,
                'budget' => $contract->net_amount,
                'completion_percentage' => 0.00,
                'status' => 'in_progress',
                'notes' => $notes ?? $contract->scope_of_work,
                'created_by' => auth()->id(),
            ]);

            // Create initial stages from payment terms / milestones if available
            $milestones = $contract->paymentTerms;
            if ($milestones->count() > 0) {
                $weight = round(100 / $milestones->count(), 2);
                foreach ($milestones as $milestone) {
                    $project->stages()->create([
                        'name' => $milestone->milestone_name,
                        'weight_percentage' => $weight,
                        'completion_percentage' => 0.00,
                        'due_date' => $milestone->due_date,
                        'status' => 'pending',
                    ]);
                }
            } else {
                // Default 3 stages
                $project->stages()->createMany([
                    ['name' => 'التوريد والتصميم المبدئي', 'weight_percentage' => 30.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
                    ['name' => 'التصنيع والتجميع الميداني', 'weight_percentage' => 50.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
                    ['name' => 'التركيب والتسليم النهائي', 'weight_percentage' => 20.0, 'completion_percentage' => 0.0, 'status' => 'pending'],
                ]);
            }

            $contract->update(['status' => 'active']);

            ActivityLog::log(
                'project_created_from_contract',
                $project,
                "Created project {$project->project_number} from contract {$contract->contract_number}"
            );

            return $project;
        });
    }

    /**
     * Recalculate project progress percentage.
     */
    public function recalculateProgress(Project $project): float
    {
        $stages = $project->stages;
        if ($stages->count() === 0) {
            return 0.0;
        }

        $totalProgress = 0.0;
        foreach ($stages as $stage) {
            $totalProgress += ($stage->weight_percentage * $stage->completion_percentage) / 100;
        }

        $overallPercentage = min(100.0, round($totalProgress, 2));
        $project->update(['completion_percentage' => $overallPercentage]);

        return $overallPercentage;
    }

    /**
     * Approve change order and update budget.
     */
    public function approveChangeOrder(ProjectChangeOrder $changeOrder): void
    {
        DB::transaction(function () use ($changeOrder) {
            if ($changeOrder->status === 'approved') {
                return;
            }

            $changeOrder->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            $project = $changeOrder->project;
            $project->increment('budget', $changeOrder->cost_impact);

            if ($changeOrder->time_impact_days > 0 && $project->expected_end_date) {
                $project->update([
                    'expected_end_date' => $project->expected_end_date->addDays($changeOrder->time_impact_days)
                ]);
            }

            ActivityLog::log(
                'change_order_approved',
                $changeOrder,
                "Approved change order {$changeOrder->order_number} for project {$project->project_number}"
            );
        });
    }

    /**
     * Calculate project profitability analytics.
     */
    public function calculateProfitability(Project $project): array
    {
        $contractAmount = $project->contract ? $project->contract->net_amount : $project->budget;
        $approvedChangeOrders = $project->changeOrders()->where('status', 'approved')->sum('cost_impact');
        $totalRevenue = $contractAmount + $approvedChangeOrders;

        $totalExpenses = $project->expenses()->sum('amount');
        $netProfit = $totalRevenue - $totalExpenses;
        $profitMargin = $totalRevenue > 0 ? round(($netProfit / $totalRevenue) * 100, 2) : 0.0;

        return [
            'contract_revenue' => $contractAmount,
            'change_orders_revenue' => $approvedChangeOrders,
            'total_revenue' => $totalRevenue,
            'total_expenses' => $totalExpenses,
            'net_profit' => $netProfit,
            'profit_margin' => $profitMargin,
        ];
    }
}
