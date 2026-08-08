<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseInvoiceItem;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\InventoryItem;
use App\Models\Unit;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PurchaseController extends Controller
{
    use AuthorizesRequests;

    public function index($locale = 'ar', Request $request = null)
    {
        $this->authorize('view-purchases');

        $purchaseInvoices = PurchaseInvoice::with(['supplier', 'warehouse', 'creator'])->latest()->paginate(15);
        return view('purchases.index', compact('purchaseInvoices'));
    }

    public function createInvoice($locale = 'ar')
    {
        $this->authorize('create-purchases');

        $suppliers = Supplier::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = InventoryItem::with(['baseUnit', 'wholesaleUnit'])->where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();

        return view('purchases.create_invoice', compact('suppliers', 'warehouses', 'items', 'units'));
    }

    public function storeInvoice($locale = 'ar', Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('create-purchases');

        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'invoice_date' => 'required|date',
            'payment_type' => 'required|in:cash,bank,credit',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoiceNumber = 'PINV-' . date('Ymd') . '-' . str_pad(PurchaseInvoice::count() + 1, 4, '0', STR_PAD_LEFT);
            $subtotal = 0;
            $totalTax = 0;

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'invoice_date' => $request->invoice_date,
                'payment_type' => $request->payment_type,
                'status' => $request->payment_type == 'credit' ? 'unpaid' : 'paid',
                'notes' => $request->notes,
                'created_by' => auth()->id(),
                'total_amount' => 0,
                'tax_amount' => 0,
                'net_amount' => 0,
            ]);

            foreach ($request->items as $row) {
                $item = InventoryItem::find($row['inventory_item_id']);
                $unit = Unit::find($row['unit_id']);

                $conversion = ($item->wholesale_unit_id == $unit->id) ? (float)$item->conversion_factor : 1.0;
                $qtyInBase = (float)$row['quantity'] * $conversion;

                $taxRate = (float) setting('tax_percentage', 15.00);
                $lineSubtotal = (float)$row['quantity'] * (float)$row['unit_price'];
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                PurchaseInvoiceItem::create([
                    'purchase_invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'quantity' => $row['quantity'],
                    'qty_in_base_units' => $qtyInBase,
                    'unit_price' => $row['unit_price'],
                    'subtotal' => $lineSubtotal,
                    'tax_amount' => $lineTax,
                    'total' => $lineTotal,
                ]);

                // Increment stock in destination warehouse
                $whItem = WarehouseItem::firstOrCreate(
                    ['warehouse_id' => $request->warehouse_id, 'inventory_item_id' => $item->id],
                    ['qty_in_base_units' => 0]
                );
                $whItem->increment('qty_in_base_units', $qtyInBase);
            }

            $netAmount = $subtotal + $totalTax;
            $invoice->update([
                'total_amount' => $subtotal,
                'tax_amount' => $totalTax,
                'net_amount' => $netAmount,
            ]);

            // Automatic Journal Entry for Purchase
            $inventoryAccount = Account::where('code', '113101')->first(); // حـ/ مخزون المركز الرئيسي
            $cashAccount = Account::where('code', '111101')->first();      // الخزينة الرئيسية
            $supplierAccount = Account::where('code', '211101')->first();  // حـ/ الموردين
            $vatAccount = Account::where('code', '212101')->first();       // حـ/ الضريبة

            if ($inventoryAccount) {
                $creditAcc = ($request->payment_type == 'credit' && $supplierAccount) ? $supplierAccount : $cashAccount;

                $je = JournalEntry::create([
                    'entry_number' => 'JE-PINV-' . $invoice->id,
                    'entry_date' => $request->invoice_date,
                    'reference_type' => PurchaseInvoice::class,
                    'reference_id' => $invoice->id,
                    'description' => "فاتورة مشتريات رقم {$invoice->invoice_number}",
                    'status' => 'posted',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                // Debit Inventory
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $inventoryAccount->id,
                    'debit' => $subtotal,
                    'credit' => 0,
                    'description' => "مشتريات فاتورة رقم {$invoice->invoice_number}",
                ]);

                // Debit VAT if applicable
                if ($totalTax > 0 && $vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $vatAccount->id,
                        'debit' => $totalTax,
                        'credit' => 0,
                        'description' => "ضريبة مشتريات مدفوعة 15%",
                    ]);
                }

                // Credit Cash / Supplier
                if ($creditAcc) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $creditAcc->id,
                        'debit' => 0,
                        'credit' => $netAmount,
                        'description' => "سداد/استحقاق فاتورة مشتريات {$invoice->invoice_number}",
                    ]);
                }
            }

            DB::commit();

            ActivityLog::log('purchase_invoice_created', $invoice, "Created purchase invoice {$invoice->invoice_number}");

            return redirect()->route('purchases.index')->with('success', 'تم تسجيل فاتورة المشتريات وتزويد رصيد المخزن والقيد المحاسبي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ فاتورة المشتريات: ' . $e->getMessage());
        }
    }

    public function showInvoice($locale = 'ar', $id = null)
    {
        $invoice = ($id instanceof PurchaseInvoice) ? $id : PurchaseInvoice::findOrFail($id);
        $this->authorize('view-purchases');
        $invoice->load(['supplier', 'warehouse', 'items.item.baseUnit', 'items.item.wholesaleUnit', 'items.unit', 'creator']);

        return view('purchases.show_invoice', compact('invoice'));
    }
}
