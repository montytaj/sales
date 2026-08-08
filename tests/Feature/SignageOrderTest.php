<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Project;
use App\Models\SignageOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SignageOrderTest extends TestCase
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

    public function test_standalone_signage_order_creation_and_design_approval(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/signage-orders', [
            'customer_id' => $this->customer->id,
            'dimensions' => '300x150 cm',
            'warranty_months' => 24,
            'maintenance_notes' => 'إضاءة لد خارجية 12V مع محول مقاوم للماء',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('signage_orders', [
            'dimensions' => '300x150 cm',
            'design_approved' => false,
            'status' => 'new',
        ]);

        $signageOrder = SignageOrder::latest()->first();

        // Approve Design
        $response = $this->post('/ar/signage-orders/' . $signageOrder->id . '/approve-design');
        $response->assertRedirect();

        $signageOrder->refresh();
        $this->assertTrue($signageOrder->design_approved);
        $this->assertEquals('manufacturing', $signageOrder->status);
    }

    public function test_project_linked_signage_order(): void
    {
        $project = Project::create([
            'project_number' => Project::generateProjectNumber(),
            'name' => 'مشروع الواجهة المركزية',
            'customer_id' => $this->customer->id,
            'start_date' => '2026-08-01',
            'budget' => 50000.00,
            'status' => 'in_progress',
        ]);

        $response = $this->actingAs($this->admin)->post('/ar/signage-orders', [
            'customer_id' => $this->customer->id,
            'project_id' => $project->id,
            'dimensions' => '500x200 cm',
            'warranty_months' => 12,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('signage_orders', [
            'project_id' => $project->id,
            'dimensions' => '500x200 cm',
        ]);
    }
}
