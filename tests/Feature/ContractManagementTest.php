<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Project;
use App\Services\ProjectService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractManagementTest extends TestCase
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

    public function test_create_contract_with_5_payment_milestones(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/contracts', [
            'customer_id' => $this->customer->id,
            'scope_of_work' => 'تصنيع وتركيب واجهات خشبية ولوافتات إعلانية متكاملة',
            'total_amount' => 100000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 15000.00,
            'start_date' => '2026-08-01',
            'end_date' => '2026-12-31',
            'milestones' => [
                ['milestone_name' => 'مقدم عقد عند التوقيع', 'due_date' => '2026-08-01', 'amount_type' => 'percentage', 'value' => 20],
                ['milestone_name' => 'دفعة التوريد وتوفير الألواح', 'due_date' => '2026-09-01', 'amount_type' => 'percentage', 'value' => 20],
                ['milestone_name' => 'دفعة اكتمال عمليات القص بالورشة', 'due_date' => '2026-10-01', 'amount_type' => 'percentage', 'value' => 30],
                ['milestone_name' => 'دفعة التسليم الابتدائي والموقع', 'due_date' => '2026-11-01', 'amount_type' => 'percentage', 'value' => 20],
                ['milestone_name' => 'دفعة التسليم النهائي وانقضاء الضمان', 'due_date' => '2026-12-31', 'amount_type' => 'percentage', 'value' => 10],
            ],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('contracts', [
            'net_amount' => 115000.00,
            'status' => 'draft',
        ]);

        $contract = Contract::where('scope_of_work', 'like', '%واجهات خشبية%')->first();
        $this->assertCount(5, $contract->paymentTerms);
        $this->assertEquals(23000.00, $contract->paymentTerms->first()->calculated_amount);
    }

    public function test_contract_approval_and_conversion_to_project(): void
    {
        $contract = Contract::create([
            'contract_number' => Contract::generateContractNumber(),
            'customer_id' => $this->customer->id,
            'scope_of_work' => 'مشروع متكامل',
            'total_amount' => 50000.00,
            'net_amount' => 57500.00,
            'start_date' => '2026-08-01',
            'status' => 'draft',
        ]);

        // Approve Contract
        $response = $this->actingAs($this->admin)->post('/ar/contracts/' . $contract->id . '/approve');
        $response->assertRedirect();

        $contract->refresh();
        $this->assertTrue($contract->is_approved);
        $this->assertEquals('approved', $contract->status);

        // Convert to Project
        $response = $this->post('/ar/contracts/' . $contract->id . '/convert-to-project');
        $response->assertRedirect();

        $this->assertDatabaseHas('projects', [
            'contract_id' => $contract->id,
            'customer_id' => $this->customer->id,
            'budget' => 57500.00,
        ]);
    }
}
