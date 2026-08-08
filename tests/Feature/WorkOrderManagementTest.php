<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\WorkOrder;
use App\Services\WorkOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkOrderManagementTest extends TestCase
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

    public function test_user_can_create_work_order(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/work-orders', [
            'customer_id' => $this->customer->id,
            'sheet_count' => 5,
            'sheet_type' => 'MDF 18mm',
            'dimensions' => '122x244 cm',
            'thickness' => '18 mm',
            'priority' => 'high',
            'due_date' => '2026-08-10',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('work_orders', [
            'sheet_count' => 5,
            'sheet_type' => 'MDF 18mm',
            'priority' => 'high',
            'status' => 'new',
        ]);
    }

    public function test_start_execution_fails_without_start_authorization(): void
    {
        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $this->customer->id,
            'sheet_count' => 1,
            'sheet_type' => 'MDF',
            'priority' => 'normal',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->post('/ar/workshop-kiosk/' . $workOrder->id . '/action', [
            'action' => 'start',
        ]);

        $response->assertSessionHas('error');
        $workOrder->refresh();
        $this->assertEquals('new', $workOrder->status);
    }

    public function test_issue_start_authorization_and_start_execution(): void
    {
        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $this->customer->id,
            'sheet_count' => 2,
            'sheet_type' => 'Acrylic',
            'priority' => 'urgent',
            'status' => 'new',
        ]);

        // Issue Start Authorization
        $response = $this->actingAs($this->admin)->post('/ar/work-orders/' . $workOrder->id . '/authorize-start');
        $response->assertRedirect();

        $workOrder->refresh();
        $this->assertNotNull($workOrder->authorization);
        $this->assertEquals('authorized_to_start', $workOrder->status);

        // Start Execution via Kiosk
        $response = $this->actingAs($this->admin)->post('/ar/workshop-kiosk/' . $workOrder->id . '/action', [
            'action' => 'start',
        ]);

        $response->assertRedirect();
        $workOrder->refresh();
        $this->assertEquals('in_progress', $workOrder->status);
    }

    public function test_exceptional_override_start_authorization(): void
    {
        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $this->customer->id,
            'sheet_count' => 1,
            'sheet_type' => 'MDF',
            'priority' => 'normal',
            'status' => 'new',
        ]);

        $response = $this->actingAs($this->admin)->post('/ar/work-orders/' . $workOrder->id . '/override-start', [
            'override_reason' => 'اعتماد استثنائي من مدير الورشة لبدء القص',
        ]);

        $response->assertRedirect();
        $workOrder->refresh();

        $this->assertTrue($workOrder->authorization->is_override);
        $this->assertEquals('اعتماد استثنائي من مدير الورشة لبدء القص', $workOrder->authorization->override_reason);
    }

    public function test_complete_work_order_and_record_waste(): void
    {
        $workOrderService = app(WorkOrderService::class);

        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $this->customer->id,
            'sheet_count' => 10,
            'sheet_type' => 'Plywood',
            'priority' => 'normal',
            'status' => 'new',
        ]);

        $this->actingAs($this->admin);

        $workOrderService->authorizeStart($workOrder);
        $workOrder->refresh();

        $workOrderService->startExecution($workOrder);
        $workOrder->refresh();

        // Complete execution
        $response = $this->post('/ar/workshop-kiosk/' . $workOrder->id . '/action', [
            'action' => 'complete',
            'good_pieces' => 9,
            'waste_pieces' => 1,
        ]);

        $response->assertRedirect();
        $workOrder->refresh();

        $this->assertEquals('ready_for_delivery', $workOrder->status);
        $this->assertEquals(9, $workOrder->good_pieces);
        $this->assertEquals(1, $workOrder->waste_pieces);
    }
}
