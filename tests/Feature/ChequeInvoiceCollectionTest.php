<?php

namespace Tests\Feature;

use App\Models\Cashbox;
use App\Models\Cheque;
use App\Models\Customer;
use App\Models\InventoryItem;
use App\Models\Invoice;
use App\Models\PaymentVoucher;
use App\Models\Unit;
use App\Models\User;
use App\Models\Warehouse;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChequeInvoiceCollectionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Customer $customer;
    protected Warehouse $warehouse;
    protected InventoryItem $item;
    protected Unit $unit;
    protected Cashbox $cashbox;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::first();
        $perm = \Spatie\Permission\Models\Permission::firstOrCreate(['name' => 'manage-cheques', 'guard_name' => 'web']);
        $this->admin->givePermissionTo($perm);

        $this->customer = Customer::first();
        $this->customer->update(['credit_limit' => 1000000.00]);
        $this->warehouse = Warehouse::first();
        $this->item = InventoryItem::first();
        $this->unit = Unit::first();
        $this->cashbox = Cashbox::first();
    }

    public function test_uncollected_cheque_does_not_mark_invoice_as_paid_until_collected(): void
    {
        // 1. Create invoice for 1000 SAR
        $invoice = Invoice::create([
            'invoice_number' => 'INV-CHQ-TEST-001',
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'status' => 'issued',
            'subtotal' => 1000.00,
            'tax_amount' => 0.00,
            'total_amount' => 1000.00,
            'due_amount' => 1000.00,
            'created_by' => $this->admin->id,
        ]);

        // 2. Create Receipt Voucher with Cheque payment (under_collection)
        $paymentService = app(PaymentService::class);
        $voucherData = [
            'type' => 'receipt',
            'customer_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
            'cashbox_id' => $this->cashbox->id,
            'amount' => 1000.00,
            'payment_date' => now()->toDateString(),
            'notes' => 'سداد فاتورة بشيك تحت التحصيل',
        ];

        $paymentLines = [
            [
                'payment_method' => 'cheque',
                'amount' => 1000.00,
            ]
        ];

        $chequeData = [
            'cheque_number' => 'CHQ-555',
            'bank_name' => 'بنك الرياض',
            'drawer_name' => $this->customer->name,
            'issue_date' => now()->toDateString(),
            'due_date' => now()->addDays(5)->toDateString(),
            'amount' => 1000.00,
        ];

        $voucher = $paymentService->createVoucher($voucherData, $paymentLines, $chequeData);



        // Verify: Invoice due_amount is STILL 1000 SAR and status is 'issued' (unpaid)
        $invoice->refresh();
        $this->assertEquals(1000.00, (float)$invoice->due_amount);
        $this->assertEquals('issued', $invoice->status);

        // 3. Now collect the cheque
        $cheque = Cheque::where('payment_voucher_id', $voucher->id)->first();
        $this->assertNotNull($cheque);
        $this->assertNotEquals('collected', $cheque->status);


        $response = $this->actingAs($this->admin)->post("/ar/cheques/{$cheque->id}/clear", [
            'cashbox_id' => $this->cashbox->id,
            'clear_date' => now()->toDateString(),
            'notes' => 'تم تحصيل الشيك وإيداعه بنجاح',
        ]);


        $response->assertSessionHas('success');

        // Verify: Cheque is collected and Invoice is now PAID with due_amount = 0 SAR!
        $cheque->refresh();
        $invoice->refresh();

        $this->assertEquals('collected', $cheque->status);
        $this->assertEquals(0.00, (float)$invoice->due_amount);
        $this->assertEquals('paid', $invoice->status);
    }
}
