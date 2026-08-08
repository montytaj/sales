<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\WorkOrder;
use App\Models\Project;
use App\Services\CostingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CostingEngineTest extends TestCase
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

    public function test_work_order_cost_breakdown_and_variance(): void
    {
        $costingService = app(CostingService::class);

        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $this->customer->id,
            'sheet_count' => 5,
            'sheet_type' => 'Acrylic',
            'priority' => 'high',
            'status' => 'new',
        ]);

        $costingService->recordCost($workOrder, 'material', 1000.00, 1050.00, 'تكلفة الألواح');
        $costingService->recordCost($workOrder, 'labor', 300.00, 300.00, 'أجور الفني');
        $costingService->recordCost($workOrder, 'machine', 200.00, 250.00, 'استهلاك الماكينة');
        $costingService->recordCost($workOrder, 'waste', 0.00, 100.00, 'لوح تالف');

        $summary = $costingService->getCostSummary($workOrder);

        $this->assertEquals(1500.00, $summary['total_estimated']);
        $this->assertEquals(1700.00, $summary['total_actual']);
        $this->assertEquals(200.00, $summary['variance']);
        $this->assertEquals(1050.00, $summary['breakdown']['material']['actual']);
    }

    public function test_project_costing_breakdown(): void
    {
        $costingService = app(CostingService::class);

        $project = Project::create([
            'project_number' => Project::generateProjectNumber(),
            'name' => 'مشروع واجهات',
            'customer_id' => $this->customer->id,
            'start_date' => '2026-08-01',
            'budget' => 50000.00,
            'status' => 'in_progress',
        ]);

        $costingService->recordCost($project, 'subcontractor', 15000.00, 15000.00, 'مقاول إضاءة');
        $costingService->recordCost($project, 'transport', 2000.00, 2200.00, 'شحن ونقل مسبق');

        $summary = $costingService->getCostSummary($project);
        $this->assertEquals(17000.00, $summary['total_estimated']);
        $this->assertEquals(17200.00, $summary['total_actual']);
    }
}
