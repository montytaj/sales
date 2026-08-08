<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Cashbox;
use App\Models\PaymentVoucher;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentVoucherTest extends TestCase
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

    public function test_cash_payment_and_invoice_reconciliation(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $this->customer->id,
            'status' => 'issued',
            'issue_date' => now(),
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 150.00,
            'total_amount' => 1150.00,
        ]);

        $initialBalance = $this->cashbox->current_balance;

        $response = $this->actingAs($this->admin)->post('/ar/payments', [
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 1150.00,
            'payment_date' => date('Y-m-d'),
            'lines' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 1150.00,
                ]
            ]
        ]);

        $response->assertRedirect();
        $this->cashbox->refresh();
        $invoice->refresh();

        $this->assertEquals($initialBalance + 1150.00, $this->cashbox->current_balance);
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_split_payment_validation(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/payments', [
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 1000.00,
            'payment_date' => date('Y-m-d'),
            'lines' => [
                [
                    'payment_method' => 'cash',
                    'amount' => 500.00,
                ],
                [
                    'payment_method' => 'card',
                    'amount' => 300.00, // Total = 800 (Mismatch with 1000)
                ]
            ]
        ]);

        $response->assertSessionHas('error');
    }

    public function test_cancel_payment_voucher_reverts_cashbox_balance(): void
    {
        $paymentService = app(PaymentService::class);

        $initialBalance = $this->cashbox->current_balance;

        $voucher = $paymentService->createVoucher([
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 500.00,
            'payment_date' => date('Y-m-d'),
        ], [
            ['payment_method' => 'cash', 'amount' => 500.00]
        ]);

        $this->cashbox->refresh();
        $this->assertEquals($initialBalance + 500.00, $this->cashbox->current_balance);

        // Cancel voucher
        $response = $this->actingAs($this->admin)->patch('/ar/payments/' . $voucher->id . '/cancel');
        $response->assertRedirect();

        $this->cashbox->refresh();
        $this->assertEquals($initialBalance, $this->cashbox->current_balance);
    }
}
