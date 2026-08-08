<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function index($locale = 'ar', Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('view-invoices');

        $query = Invoice::with(['customer', 'branch', 'warehouse']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $invoices = $query->latest()->paginate(15)->withQueryString();

        return view('sales.invoices.index', compact('invoices'));
    }

    public function create($locale = 'ar')
    {
        $this->authorize('create-invoices');

        $customers = Customer::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = InventoryItem::with(['baseUnit', 'wholesaleUnit'])->where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $categories = ItemCategory::where('is_active', true)->get();

        return view('sales.invoices.create', compact('customers', 'warehouses', 'items', 'units', 'categories'));
    }

    public function store($locale = 'ar', Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('create-invoices');

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'warehouse_id' => 'required|exists:warehouses,id',
            'issue_date' => 'required|date',
            'payment_type' => 'required|in:cash,bank,credit',
            'items' => 'required|array|min:1',
            'items.*.inventory_item_id' => 'required|exists:inventory_items,id',
            'items.*.unit_id' => 'required|exists:units,id',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad(Invoice::count() + 1, 4, '0', STR_PAD_LEFT);
            $subtotal = 0;
            $totalTax = 0;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $request->customer_id,
                'warehouse_id' => $request->warehouse_id,
                'branch_id' => auth()->user()?->main_branch_id,
                'created_by' => auth()->id(),
                'issue_date' => $request->issue_date,
                'due_date' => $request->due_date ?? $request->issue_date,
                'payment_type' => $request->payment_type,
                'status' => $request->payment_type == 'credit' ? 'issued' : 'paid',
                'notes' => $request->notes,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            foreach ($request->items as $row) {
                $item = InventoryItem::find($row['inventory_item_id']);
                $unit = Unit::find($row['unit_id']);

                // Calculate base quantity (if selling by wholesale unit, multiply by conversion factor)
                $conversion = ($item->wholesale_unit_id == $unit->id) ? (float)$item->conversion_factor : 1.0;
                $qtyInBase = (float)$row['quantity'] * $conversion;

                $taxRate = (float) setting('tax_percentage', 15.00);
                $lineSubtotal = (float)$row['quantity'] * (float)$row['unit_price'];
                $lineTax = $lineSubtotal * ($taxRate / 100);
                $lineTotal = $lineSubtotal + $lineTax;

                $subtotal += $lineSubtotal;
                $totalTax += $lineTax;

                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'item_name' => $item->name,
                    'quantity' => $row['quantity'],
                    'qty_in_base_units' => $qtyInBase,
                    'unit_price' => $row['unit_price'],
                    'tax_percent' => $taxRate,
                    'tax_amount' => $lineTax,
                    'subtotal' => $lineSubtotal,
                    'total' => $lineTotal,
                ]);

                // Deduct stock from destination warehouse
                $whItem = WarehouseItem::firstOrCreate(
                    ['warehouse_id' => $request->warehouse_id, 'inventory_item_id' => $item->id],
                    ['qty_in_base_units' => 0]
                );
                $whItem->decrement('qty_in_base_units', $qtyInBase);
            }

            $totalAmount = $subtotal + $totalTax;
            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
            ]);

            // Automatic Journal Entry on 5-Level Chart of Accounts
            $cashAccount = Account::where('code', '111101')->first(); // الخزينة الرئيسية
            $arAccount = Account::where('code', '112101')->first();   // حـ/ العملاء
            $salesAccount = Account::where('code', '411101')->first(); // حـ/ مبيعات الجملة
            $vatAccount = Account::where('code', '212101')->first();   // حـ/ الضريبة

            if ($cashAccount && $salesAccount) {
                $debitAcc = ($request->payment_type == 'credit' && $arAccount) ? $arAccount : $cashAccount;

                $je = JournalEntry::create([
                    'entry_number' => 'JE-INV-' . $invoice->id,
                    'entry_date' => $request->issue_date,
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'description' => "فاتورة مبيعات رقم {$invoice->invoice_number}",
                    'status' => 'posted',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                // Debit Cash / Customer
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $debitAcc->id,
                    'debit' => $totalAmount,
                    'credit' => 0,
                    'description' => "مبيعات فاتورة {$invoice->invoice_number}",
                ]);

                // Credit Sales Revenue
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $salesAccount->id,
                    'debit' => 0,
                    'credit' => $subtotal,
                    'description' => "إيراد مبيعات فاتورة {$invoice->invoice_number}",
                ]);

                // Credit VAT
                if ($totalTax > 0 && $vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $vatAccount->id,
                        'debit' => 0,
                        'credit' => $totalTax,
                        'description' => "ضريبة قيمة مضافة 15%",
                    ]);
                }
            }

            DB::commit();

            ActivityLog::log('invoice_created', $invoice, "Created invoice {$invoice->invoice_number}");

            return redirect()->route('invoices.index')->with('success', 'تم إنشاء فاتورة المبيعات وخصم الكميات وتسجيل القيد المحاسبي بنجاح');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'حدث خطأ أثناء حفظ الفاتورة: ' . $e->getMessage());
        }
    }

    public function show($locale = 'ar', $id = null)
    {
        $invoice = ($id instanceof Invoice) ? $id : Invoice::findOrFail($id);
        $this->authorize('view-invoices');
        $invoice->load(['customer', 'branch', 'warehouse', 'items.item.baseUnit', 'items.item.wholesaleUnit', 'items.unit', 'creator']);

        return view('sales.invoices.show', compact('invoice'));
    }

    public function print($locale = 'ar', $id = null)
    {
        $invoice = ($id instanceof Invoice) ? $id : Invoice::findOrFail($id);
        $this->authorize('print-sales-documents');
        $invoice->load(['customer', 'branch', 'warehouse', 'items.item.baseUnit', 'items.item.wholesaleUnit', 'items.unit', 'creator']);

        return view('sales.invoices.print', compact('invoice'));
    }

    public function updateStatus($locale = 'ar', Request $request = null, $id = null)
    {
        $invoice = ($id instanceof Invoice) ? $id : Invoice::findOrFail($id);
        $request = $request ?? request();

        $request->validate([
            'status' => 'required|in:draft,issued,paid,partially_paid,cancelled',
        ]);

        $invoice->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'تم تحديث حالة الفاتورة بنجاح');
    }
}
