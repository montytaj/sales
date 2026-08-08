<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesInvoiceTest extends TestCase
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

    public function test_authorized_user_can_view_invoices(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/invoices');

        $response->assertStatus(200);
        $response->assertSee(__('sales.invoices_list'));
    }

    public function test_user_can_update_invoice_status(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $this->customer->id,
            'status' => 'issued',
            'issue_date' => now(),
            'subtotal' => 500.00,
            'discount_amount' => 0.00,
            'tax_amount' => 75.00,
            'total_amount' => 575.00,
        ]);

        $response = $this->actingAs($this->admin)->patch('/ar/invoices/' . $invoice->id . '/update-status', [
            'status' => 'paid',
        ]);

        $response->assertRedirect();
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_printable_invoice_view_accessible(): void
    {
        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'customer_id' => $this->customer->id,
            'status' => 'issued',
            'issue_date' => now(),
            'subtotal' => 500.00,
            'discount_amount' => 0.00,
            'tax_amount' => 75.00,
            'total_amount' => 575.00,
        ]);

        $response = $this->actingAs($this->admin)->get('/ar/invoices/' . $invoice->id . '/print');
        $response->assertStatus(200);
        $response->assertSee('فاتورة مبيعات ضريبية');
    }
}
