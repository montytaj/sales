<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Quotation;
use App\Models\Invoice;
use App\Models\CustomerOrder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SalesQuotationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Service $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->customer = Customer::first();
        $this->service = Service::first();
    }

    public function test_authorized_user_can_view_quotations(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/quotations');

        $response->assertStatus(200);
        $response->assertSee(__('sales.quotations_list'));
    }

    public function test_server_side_calculation_and_quotation_creation(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/quotations', [
            'customer_id' => $this->customer->id,
            'issue_date' => '2026-08-03',
            'expiry_date' => '2026-08-17',
            'items' => [
                [
                    'service_id' => $this->service->id,
                    'item_name' => 'قص وتفريغ أخشاب CNC',
                    'quantity' => 10,
                    'unit_of_measure' => 'm2',
                    'unit_price' => 100.00,
                    'discount_amount' => 50.00, // Subtotal after discount = 950.00
                    'tax_percent' => 15.00,     // Tax = 142.50, Total = 1092.50
                ]
            ]
        ]);

        $quotation = Quotation::orderBy('id', 'desc')->first();
        $this->assertNotNull($quotation);
        $this->assertEquals(950.00, $quotation->subtotal);
        $this->assertEquals(50.00, $quotation->discount_amount);
        $this->assertEquals(142.50, $quotation->tax_amount);
        $this->assertEquals(1092.50, $quotation->total_amount);
    }

    public function test_quotation_approval_and_locking(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateQuotationNumber(),
            'customer_id' => $this->customer->id,
            'status' => 'draft',
            'issue_date' => now(),
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 150.00,
            'total_amount' => 1150.00,
        ]);

        // Approve quotation
        $response = $this->actingAs($this->admin)->patch('/ar/quotations/' . $quotation->id . '/approve');
        $response->assertRedirect();

        $quotation->refresh();
        $this->assertTrue($quotation->is_approved);
    }

    public function test_convert_accepted_quotation_to_invoice(): void
    {
        $quotation = Quotation::create([
            'quotation_number' => Quotation::generateQuotationNumber(),
            'customer_id' => $this->customer->id,
            'status' => 'accepted',
            'is_approved' => true,
            'issue_date' => now(),
            'subtotal' => 1000.00,
            'discount_amount' => 0.00,
            'tax_amount' => 150.00,
            'total_amount' => 1150.00,
        ]);

        $quotation->items()->create([
            'service_id' => $this->service->id,
            'item_name' => 'خدمة تجهيز أثاث',
            'quantity' => 1,
            'unit_of_measure' => 'piece',
            'unit_price' => 1000.00,
            'discount_amount' => 0.00,
            'tax_percent' => 15.00,
            'tax_amount' => 150.00,
            'subtotal' => 1000.00,
            'total' => 1150.00,
        ]);

        $response = $this->actingAs($this->admin)->post('/ar/quotations/' . $quotation->id . '/convert-to-invoice');
        
        $invoice = Invoice::where('quotation_id', $quotation->id)->first();
        $this->assertNotNull($invoice);
        $this->assertEquals(1150.00, $invoice->total_amount);
    }
}
