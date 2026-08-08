<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Cashbox;
use App\Models\Cheque;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChequeManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Cashbox $cashbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = Customer::first();
        $this->cashbox = Cashbox::first();
    }

    public function test_cheque_creation_via_payment_voucher(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/payments', [
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 5000.00,
            'payment_date' => date('Y-m-d'),
            'lines' => [
                [
                    'payment_method' => 'cheque',
                    'amount' => 5000.00,
                ]
            ],
            'cheque_number' => 'CHK-9988',
            'bank_name' => 'مصرف الراجحي',
            'drawer_name' => 'شركة الأعمال الحديثة',
            'issue_date' => '2026-08-03',
            'due_date' => '2026-09-03',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cheques', [
            'cheque_number' => 'CHK-9988',
            'bank_name' => 'مصرف الراجحي',
            'amount' => 5000.00,
            'status' => 'received',
        ]);
    }

    public function test_user_can_update_cheque_status_to_returned(): void
    {
        $cheque = Cheque::create([
            'cheque_number' => 'CHK-1001',
            'bank_name' => 'البنك الأهلي',
            'drawer_name' => 'العميل التجريبي',
            'amount' => 2500.00,
            'issue_date' => now(),
            'due_date' => now(),
            'status' => 'received',
        ]);

        $response = $this->actingAs($this->admin)->patch('/ar/cheques/' . $cheque->id . '/update-status', [
            'status' => 'returned',
            'notes' => 'عدم كفاية الرصيد',
        ]);

        $response->assertRedirect();
        $cheque->refresh();
        $this->assertEquals('returned', $cheque->status);
    }
}
