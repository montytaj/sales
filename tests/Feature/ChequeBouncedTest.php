<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Cashbox;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\JournalEntry;
use App\Models\PaymentVoucher;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChequeBouncedTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Cashbox $cashbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::first();
        $this->customer = Customer::first();
        $this->customer->update(['credit_limit' => 1000000.00]);
        $this->cashbox = Cashbox::first();
    }

    public function test_bouncing_cheque_creates_reversing_journal_entry_and_restores_customer_debt(): void
    {
        // 1. Create a Payment Voucher with cheque
        $voucher = PaymentVoucher::create([
            'voucher_number' => 'RCT-TEST-001',
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'status' => 'posted',
            'created_by' => $this->admin->id,
        ]);

        $cheque = Cheque::create([
            'payment_voucher_id' => $voucher->id,
            'cheque_number' => 'CHQ-999',
            'bank_name' => 'بنك الراجحي',
            'drawer_name' => $this->customer->name,
            'amount' => 1000.00,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'status' => 'under_collection',
            'type' => 'incoming',
            'cashbox_id' => $this->cashbox->id,
            'created_by' => $this->admin->id,
        ]);

        // 2. Bounce the cheque
        $response = $this->actingAs($this->admin)->post("/ar/cheques/{$cheque->id}/bounce", [
            'notes' => 'عدم كفاية الرصيد الحسابي بالحساب المقابل',
        ]);

        $response->assertSessionHas('warning');

        $cheque->refresh();
        $this->assertEquals('returned', $cheque->status);

        // 3. Verify Reversing Journal Entry created
        $bounceJe = JournalEntry::where('entry_number', 'JE-BOUNCE-CHQ-' . $cheque->id)->first();
        $this->assertNotNull($bounceJe);
        $this->assertEquals(2, $bounceJe->lines->count());

        // Check debit line is Customer AR account (restoring debt)
        $debitLine = $bounceJe->lines->where('debit', '>', 0)->first();
        $this->assertNotNull($debitLine);
        $this->assertEquals(1000.00, (float)$debitLine->debit);
    }
}
