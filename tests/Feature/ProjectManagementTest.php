<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ProjectStage;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = Customer::first();
    }

    public function test_multi_stage_project_progress_calculation(): void
    {
        $projectService = app(ProjectService::class);

        $project = Project::create([
            'project_number' => Project::generateProjectNumber(),
            'name' => 'مشروع تجهيز فرع رئيسي',
            'customer_id' => $this->customer->id,
            'start_date' => '2026-08-01',
            'budget' => 100000.00,
            'completion_percentage' => 0.00,
            'status' => 'in_progress',
        ]);

        $project->stages()->createMany([
            ['name' => 'المعاينة والتوريد', 'weight_percentage' => 40.0, 'completion_percentage' => 100.0, 'status' => 'completed'],
            ['name' => 'التصنيع والتركيب', 'weight_percentage' => 60.0, 'completion_percentage' => 50.0, 'status' => 'in_progress'],
        ]);

        $progress = $projectService->recalculateProgress($project);

        // Progress = (40 * 100 / 100) + (60 * 50 / 100) = 40 + 30 = 70%
        $this->assertEquals(70.0, $progress);
        $this->assertEquals(70.0, $project->fresh()->completion_percentage);
    }

    public function test_project_change_order_and_profitability_calculation(): void
    {
        $projectService = app(ProjectService::class);

        $project = Project::create([
            'project_number' => Project::generateProjectNumber(),
            'name' => 'مشروع بناء لافتات',
            'customer_id' => $this->customer->id,
            'start_date' => '2026-08-01',
            'budget' => 100000.00,
            'status' => 'in_progress',
        ]);

        // Add Expenses
        $project->expenses()->create([
            'type' => 'material',
            'description' => 'شراء ألواح خشب وإكريليك',
            'amount' => 40000.00,
            'expense_date' => '2026-08-02',
        ]);

        $project->expenses()->create([
            'type' => 'subcontractor',
            'description' => 'عقد مقاول تركيبات إضاءة',
            'amount' => 20000.00,
            'expense_date' => '2026-08-03',
        ]);

        // Add and approve change order
        $changeOrder = $project->changeOrders()->create([
            'order_number' => 'PCO-2026-00001',
            'description' => 'توسعة إضافية للواجهة الجانبية',
            'cost_impact' => 15000.00,
            'time_impact_days' => 5,
            'status' => 'pending',
        ]);

        $this->actingAs($this->admin);
        $projectService->approveChangeOrder($changeOrder);

        $profitability = $projectService->calculateProfitability($project);

        // Revenue = 100,000 + 15,000 = 115,000. Expenses = 60,000. Profit = 55,000 (Margin 47.83%)
        $this->assertEquals(115000.00, $profitability['total_revenue']);
        $this->assertEquals(60000.00, $profitability['total_expenses']);
        $this->assertEquals(55000.00, $profitability['net_profit']);
        $this->assertEquals(47.83, $profitability['profit_margin']);
    }
}
