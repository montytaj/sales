<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\InventoryItem;
use App\Models\Unit;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\PurchaseInvoice;
use App\Models\JournalEntry;
use App\Models\StockMovement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceCancellationTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Warehouse $warehouse;
    protected InventoryItem $item;
    protected Unit $unit;
    protected Customer $customer;
    protected Supplier $supplier;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::where('email', 'admin@example.com')->first()
            ?: User::where('email', 'admin@a.a')->first()
            ?: User::first();

        $this->warehouse = Warehouse::first();
        $this->item = InventoryItem::first();
        $this->unit = Unit::first();
        $this->customer = Customer::first();
        $this->customer->update(['credit_limit' => 1000000.00]);
        $this->supplier = Supplier::first();
    }


    public function test_sales_invoice_cancellation_reverts_stock_and_creates_reversal_journal_entry(): void
    {
        // Set initial stock to 100 units
        WarehouseItem::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'inventory_item_id' => $this->item->id],
            ['qty_in_base_units' => 100]
        );

        // 1. Create Sales Invoice (Quantity = 10)
        $invoiceData = [
            'customer_id' => $this->customer->id,
            'warehouse_id' => $this->warehouse->id,
            'issue_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'items' => [
                [
                    'inventory_item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 10,
                    'unit_price' => 50,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post('/ar/invoices', $invoiceData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/ar/invoices');


        $invoice = Invoice::latest('id')->first();
        $this->assertNotEquals('cancelled', $invoice->status);



        // Check stock deducted (100 - 10 = 90)
        $whItem = WarehouseItem::where('warehouse_id', $invoice->warehouse_id)
            ->where('inventory_item_id', $this->item->id)
            ->first();

        $this->assertEquals(90, (float)$whItem->qty_in_base_units);

        // Check original journal entry exists
        $originalJe = JournalEntry::where('entry_number', 'JE-INV-' . $invoice->id)->first()
            ?? JournalEntry::where('reference_id', $invoice->id)->first();
        $this->assertNotNull($originalJe);


        // 2. Cancel the Sales Invoice
        $cancelResponse = $this->actingAs($this->admin)->patch("/ar/invoices/{$invoice->id}/update-status", [
            'status' => 'cancelled',
        ]);
        $cancelResponse->assertSessionHas('success');

        $invoice->refresh();
        $this->assertEquals('cancelled', $invoice->status);
        $this->assertEquals(0, (float)$invoice->due_amount);

        // Check stock restored back to 100
        $whItem->refresh();
        $this->assertEquals(100, (float)$whItem->qty_in_base_units);

        // Check reversing stock movement logged
        $stockMovement = StockMovement::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->where('movement_type', 'in')
            ->first();
        $this->assertNotNull($stockMovement);
        $this->assertEquals(10, (float)$stockMovement->quantity);

        // Check reversing journal entry created (Storno)
        $reversalJe = JournalEntry::where('reference_type', Invoice::class)
            ->where('reference_id', $invoice->id)
            ->where('entry_number', 'like', 'JE-REV-INV-%')
            ->first();
        $this->assertNotNull($reversalJe);
        $this->assertEquals($originalJe->lines->sum('debit'), $reversalJe->lines->sum('credit'));
        $this->assertEquals($originalJe->lines->sum('credit'), $reversalJe->lines->sum('debit'));
    }

    public function test_purchase_invoice_cancellation_reverts_stock_and_creates_reversal_journal_entry(): void
    {
        // Set initial stock to 50 units
        WarehouseItem::updateOrCreate(
            ['warehouse_id' => $this->warehouse->id, 'inventory_item_id' => $this->item->id],
            ['qty_in_base_units' => 50]
        );

        // 1. Create Purchase Invoice (Quantity = 20)
        $pInvoiceData = [
            'supplier_id' => $this->supplier->id,
            'warehouse_id' => $this->warehouse->id,
            'invoice_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'items' => [
                [
                    'inventory_item_id' => $this->item->id,
                    'unit_id' => $this->unit->id,
                    'quantity' => 20,
                    'unit_price' => 30,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post('/ar/purchases/store-invoice', $pInvoiceData);
        $response->assertRedirect('/ar/purchases');

        $pInvoice = PurchaseInvoice::latest('id')->first();
        $this->assertEquals('unpaid', $pInvoice->status);

        // Check stock increased (50 + 20 = 70)
        $whItem = WarehouseItem::where('warehouse_id', $pInvoice->warehouse_id)
            ->where('inventory_item_id', $this->item->id)
            ->first();

        $this->assertEquals(70, (float)$whItem->qty_in_base_units);

        // Check original journal entry
        $originalJe = JournalEntry::where('reference_type', PurchaseInvoice::class)
            ->where('reference_id', $pInvoice->id)
            ->first();
        $this->assertNotNull($originalJe);

        // 2. Cancel the Purchase Invoice
        $cancelResponse = $this->actingAs($this->admin)->post("/ar/purchases/{$pInvoice->id}/cancel");
        $cancelResponse->assertSessionHas('success');

        $pInvoice->refresh();
        $this->assertEquals('cancelled', $pInvoice->status);
        $this->assertEquals(0, (float)$pInvoice->due_amount);

        // Check stock decremented back to 50
        $whItem->refresh();
        $this->assertEquals(50, (float)$whItem->qty_in_base_units);

        // Check reversing stock movement logged
        $stockMovement = StockMovement::where('reference_type', PurchaseInvoice::class)
            ->where('reference_id', $pInvoice->id)
            ->where('movement_type', 'out')
            ->first();
        $this->assertNotNull($stockMovement);
        $this->assertEquals(20, (float)$stockMovement->quantity);

        // Check reversing journal entry created
        $reversalJe = JournalEntry::where('reference_type', PurchaseInvoice::class)
            ->where('reference_id', $pInvoice->id)
            ->where('entry_number', 'like', 'JE-REV-PINV-%')
            ->first();
        $this->assertNotNull($reversalJe);
        $this->assertEquals($originalJe->lines->sum('debit'), $reversalJe->lines->sum('credit'));
        $this->assertEquals($originalJe->lines->sum('credit'), $reversalJe->lines->sum('debit'));
    }
}
