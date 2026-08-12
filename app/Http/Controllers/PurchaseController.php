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
use App\Services\AccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StorePurchaseInvoiceRequest;
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

        $cashAccounts = Account::where('is_selectable', true)
            ->where(function ($q) {
                $q->where('code', 'like', '1111%')
                  ->orWhere('name', 'like', '%خزينة%')
                  ->orWhere('name', 'like', '%صندوق%')
                  ->orWhere('name', 'like', '%نقد%');
            })->get();
        if ($cashAccounts->isEmpty()) {
            $cashAccounts = Account::where('is_selectable', true)->where('type', 'asset')->get();
        }

        $bankAccounts = Account::where('is_selectable', true)
            ->where(function ($q) {
                $q->where('code', 'like', '1112%')
                  ->orWhere('name', 'like', '%بنك%')
                  ->orWhere('name', 'like', '%مصرف%')
                  ->orWhere('name', 'like', '%شبكة%');
            })->get();
        if ($bankAccounts->isEmpty()) {
            $bankAccounts = Account::where('is_selectable', true)->where('type', 'asset')->get();
        }

        return view('purchases.create_invoice', compact('suppliers', 'warehouses', 'items', 'units', 'cashAccounts', 'bankAccounts'));
    }

    public function storeInvoice($locale = 'ar', StorePurchaseInvoiceRequest $request = null)
    {
        $request = $request ?? request();
        // Validate that invoice date is not within a closed financial period
        try {
            app(\App\Services\AccountingService::class)->validatePeriodNotLocked($request->input('invoice_date', date('Y-m-d')));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        DB::beginTransaction();
        try {
            $maxId = (int) DB::table('purchase_invoices')->max('id');
            $invoiceNumber = 'PINV-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
            $subtotal = 0;
            $totalTax = 0;

            $invoice = PurchaseInvoice::create([
                'invoice_number' => $invoiceNumber,
                'supplier_id' => $request->supplier_id,
                'warehouse_id' => $request->warehouse_id,
                'invoice_date' => $request->invoice_date,
                'payment_type' => $request->payment_type,
                'status' => 'draft',
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

                // Record stock movement for purchase
                \App\Models\StockMovement::create([
                    'warehouse_id' => $request->warehouse_id,
                    'item_id' => $item->id,
                    'movement_type' => 'in',
                    'quantity' => $qtyInBase,
                    'reference_type' => PurchaseInvoice::class,
                    'reference_id' => $invoice->id,
                    'notes' => "فاتورة مشتريات رقم {$invoice->invoice_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            $netAmount = $subtotal + $totalTax;

            // Calculate Split Payments for Purchases
            $cashAmount = 0.0;
            $bankAmount = 0.0;
            $dueAmount = 0.0;
            $cashAccountId = $request->cash_account_id;
            $bankAccountId = $request->bank_account_id;

            if ($request->payment_type === 'cash') {
                $cashAmount = $netAmount;
                if (!$cashAccountId) {
                    $cashAccountId = AccountResolver::getCashboxAccount()?->id;
                }
            } elseif ($request->payment_type === 'bank') {
                $bankAmount = $netAmount;
                if (!$bankAccountId) {
                    $bankAccountId = AccountResolver::getBankAccount()?->id;
                }
            }
 elseif ($request->payment_type === 'credit') {
                $dueAmount = $netAmount;
            } elseif ($request->payment_type === 'split') {
                $cashAmount = min($netAmount, max(0, (float)$request->cash_amount));
                $bankAmount = min($netAmount - $cashAmount, max(0, (float)$request->bank_amount));
                $dueAmount = max(0, $netAmount - $cashAmount - $bankAmount);
            }

            $status = 'unpaid';
            if ($dueAmount <= 0.001) {
                $status = 'paid';
            } elseif (($cashAmount + $bankAmount) > 0) {
                $status = 'partially_paid';
            }

            $invoice->update([
                'total_amount' => $subtotal,
                'tax_amount' => $totalTax,
                'net_amount' => $netAmount,
                'cash_amount' => $cashAmount,
                'bank_amount' => $bankAmount,
                'due_amount' => $dueAmount,
                'cash_account_id' => $cashAccountId,
                'bank_account_id' => $bankAccountId,
                'status' => $status,
            ]);

            // Automatic Multi-Line Journal Entry for Purchase
            $inventoryAccount = AccountResolver::getPurchaseInventoryAccount();
            $supplierModel = Supplier::find($request->supplier_id);
            $supplierAccount = AccountResolver::getSupplierAccount($supplierModel);
            $vatAccount = AccountResolver::getVatAccount();

            if ($inventoryAccount) {

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

                // 1. Debit Inventory
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $inventoryAccount->id,
                    'debit' => $subtotal,
                    'credit' => 0,
                    'description' => "مشتريات فاتورة رقم {$invoice->invoice_number}",
                ]);

                // 2. Debit VAT if applicable
                if ($totalTax > 0 && $vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $vatAccount->id,
                        'debit' => $totalTax,
                        'credit' => 0,
                        'description' => "ضريبة مشتريات مدفوعة ({$taxRate}%)",
                    ]);
                }


                // 3. Credit Cash Account if cash paid
                if ($cashAmount > 0) {
                    $cAccId = $cashAccountId ?: AccountResolver::getCashboxAccount()?->id;
                    if ($cAccId) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $je->id,
                            'account_id' => $cAccId,
                            'debit' => 0,
                            'credit' => $cashAmount,
                            'description' => "سداد نقدي فاتورة مشتريات {$invoice->invoice_number}",
                        ]);
                    }
                }

                // 4. Credit Bank Account if bank paid
                if ($bankAmount > 0) {
                    $bAccId = $bankAccountId ?: AccountResolver::getBankAccount()?->id;

                    if ($bAccId) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $je->id,
                            'account_id' => $bAccId,
                            'debit' => 0,
                            'credit' => $bankAmount,
                            'description' => "سداد بنكي فاتورة مشتريات {$invoice->invoice_number}",
                        ]);
                    }
                }

                // 5. Credit Supplier (AP) if due amount
                if ($dueAmount > 0 && $supplierAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $supplierAccount->id,
                        'debit' => 0,
                        'credit' => $dueAmount,
                        'description' => "استحقاق للمورد فاتورة مشتريات {$invoice->invoice_number}",
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
        $invoice->load(['supplier', 'warehouse', 'items.item.baseUnit', 'items.item.wholesaleUnit', 'items.unit', 'creator', 'cashAccount', 'bankAccount']);

        return view('purchases.show_invoice', compact('invoice'));
    }

    public function storePo(Request $request, $locale = 'ar')
    {
        $this->authorize('create-purchases');

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'total_amount' => 'required|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'order_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $total = (float) $validated['total_amount'];
        $tax = (float) ($validated['tax_amount'] ?? 0);
        $net = $total + $tax;

        $po = PurchaseOrder::create([
            'po_number' => PurchaseOrder::generatePoNumber(),
            'supplier_id' => $validated['supplier_id'],
            'total_amount' => $total,
            'tax_amount' => $tax,
            'net_amount' => $net,
            'status' => 'issued',
            'order_date' => $validated['order_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log('purchase_order_created', $po, "Created purchase order {$po->po_number}");

        return redirect()->route('purchases.index')->with('success', 'تم إنشاء أمر الشراء بنجاح.');
    }

    public function receiveGoods(Request $request, $locale = 'ar', $id = null)
    {
        $this->authorize('manage-purchases');

        $po = ($id instanceof PurchaseOrder) ? $id : PurchaseOrder::findOrFail($id);

        $validated = $request->validate([
            'warehouse_id' => 'required|exists:warehouses,id',
            'receipt_date' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $grn = \App\Models\GoodsReceipt::create([
            'receipt_number' => \App\Models\GoodsReceipt::generateReceiptNumber(),
            'purchase_order_id' => $po->id,
            'warehouse_id' => $validated['warehouse_id'],
            'receipt_date' => $validated['receipt_date'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        $po->update(['status' => 'received']);

        ActivityLog::log('goods_received', $grn, "Received goods for PO {$po->po_number}");

        return redirect()->route('purchases.index')->with('success', 'تم تسجيل استلام البضائع بنجاح.');
    }

    public function payables($locale = 'ar', ?Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('view-purchases');

        $query = PurchaseInvoice::with(['supplier', 'warehouse', 'creator', 'payments'])
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_amount', '>', 0);

        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->input('supplier_id'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        $payables = $query->latest()->paginate(15)->withQueryString();

        $totalOutstanding = PurchaseInvoice::whereNotIn('status', ['paid', 'cancelled'])
            ->where('due_amount', '>', 0)
            ->sum('due_amount');
        $unpaidCount = PurchaseInvoice::where('status', 'unpaid')->where('due_amount', '>', 0)->count();
        $partiallyPaidCount = PurchaseInvoice::where('status', 'partially_paid')->where('due_amount', '>', 0)->count();

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();

        return view('purchases.payables', compact('payables', 'totalOutstanding', 'unpaidCount', 'partiallyPaidCount', 'suppliers'));
    }

    public function payInvoice($locale = 'ar', $id = null)
    {
        $this->authorize('create-purchases');

        $invoice = ($id instanceof PurchaseInvoice) ? $id : PurchaseInvoice::findOrFail($id);
        $invoice->load(['supplier', 'warehouse', 'payments']);

        $cashboxes = \App\Models\Cashbox::where('is_active', true)->get();
        $accounts = \App\Models\Account::where('is_selectable', true)->orderBy('code')->get();

        return view('purchases.pay_invoice', compact('invoice', 'cashboxes', 'accounts'));
    }

    public function storeInvoicePayment(Request $request, $locale = 'ar', $id = null)
    {
        $this->authorize('create-purchases');

        $invoice = ($id instanceof PurchaseInvoice) ? $id : PurchaseInvoice::findOrFail($id);

        $validated = $request->validate([
            'cashbox_id' => ['required', 'exists:cashboxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:' . $invoice->due_amount],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['required', 'in:cash,bank_transfer,card,cheque,credit,e_wallet,other'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],

            'cheque_number' => ['nullable', 'string', 'required_if:payment_method,cheque'],
            'bank_name' => ['nullable', 'string', 'required_if:payment_method,cheque'],
            'drawer_name' => ['nullable', 'string', 'required_if:payment_method,cheque'],
            'issue_date' => ['nullable', 'date', 'required_if:payment_method,cheque'],
            'due_date' => ['nullable', 'date', 'required_if:payment_method,cheque'],
        ]);

        try {
            $paymentLines = [
                [
                    'payment_method' => $validated['payment_method'],
                    'account_id' => $validated['account_id'] ?? null,
                    'amount' => $validated['amount'],
                    'reference_number' => $validated['reference_number'] ?? null,
                    'notes' => $validated['notes'] ?? null,
                ]
            ];

            $chequeData = [];
            if ($validated['payment_method'] === 'cheque') {
                $chequeData = [
                    'cheque_number' => $validated['cheque_number'],
                    'bank_name' => $validated['bank_name'],
                    'drawer_name' => $validated['drawer_name'],
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null,
                ];
            }

            $voucherData = [
                'type' => 'payment',
                'supplier_id' => $invoice->supplier_id,
                'purchase_invoice_id' => $invoice->id,
                'cashbox_id' => $validated['cashbox_id'],
                'amount' => $validated['amount'],
                'payment_date' => $validated['payment_date'],
                'notes' => $validated['notes'] ?? ("سداد دفعة فاتورة مشتريات رقم " . $invoice->invoice_number),
            ];

            $paymentService = app(\App\Services\PaymentService::class);
            $voucher = $paymentService->createVoucher($voucherData, $paymentLines, $chequeData);

            return redirect()->route('purchases.payables')->with('success', "تم تسديد مبلغ (" . number_format($validated['amount'], 2) . ") لفاتورة المشتريات ({$invoice->invoice_number}) وتوليد سند الصرف والقيد المحاسبي بنجاح.");
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء تنفيذ عملية السداد: ' . $e->getMessage());
        }
    }

    public function cancelInvoice($locale = 'ar', $id = null)
    {
        $this->authorize('create-purchases');
        $invoice = ($id instanceof PurchaseInvoice) ? $id : PurchaseInvoice::findOrFail($id);

        if ($invoice->status === 'cancelled') {
            return redirect()->back()->with('warning', 'فاتورة المشتريات ملغاة بالفعل مسبقاً.');
        }

        DB::transaction(function () use ($invoice) {
            $invoice->load(['items.item', 'warehouse']);

            // 1. Revert stock addition (Deduct from warehouse item stock and record movement out)
            foreach ($invoice->items as $pItem) {
                $item = $pItem->item;
                if (!$item) continue;

                $qtyInBase = (float) (($pItem->qty_in_base_units > 0) ? $pItem->qty_in_base_units : $pItem->quantity);


                $whItem = WarehouseItem::where('warehouse_id', $invoice->warehouse_id)
                    ->where('inventory_item_id', $item->id)
                    ->first();

                if ($whItem) {
                    $newQty = max(0, (float)$whItem->qty_in_base_units - $qtyInBase);
                    $whItem->update(['qty_in_base_units' => $newQty]);
                }

                \App\Models\StockMovement::create([
                    'warehouse_id' => $invoice->warehouse_id,
                    'item_id' => $item->id,
                    'movement_type' => 'out',
                    'quantity' => $qtyInBase,
                    'reference_type' => PurchaseInvoice::class,
                    'reference_id' => $invoice->id,
                    'notes' => "عكس إيراد مخزون - إلغاء فاتورة مشتريات رقم {$invoice->invoice_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            // 2. Reverse accounting journal entry (Storno)
            $originalJe = JournalEntry::where(function ($q) use ($invoice) {
                    $q->where(function ($sub) use ($invoice) {
                        $sub->where('reference_type', PurchaseInvoice::class)
                           ->where('reference_id', $invoice->id);
                    })->orWhere('entry_number', 'JE-PINV-' . $invoice->id);
                })
                ->where('status', 'posted')
                ->first();

            if ($originalJe) {
                $existingRev = JournalEntry::where('entry_number', 'JE-REV-PINV-' . $invoice->id)->first();


                if (!$existingRev) {
                    $reversalJe = JournalEntry::create([
                        'entry_number' => 'JE-REV-PINV-' . $invoice->id,
                        'entry_date' => now()->toDateString(),
                        'reference_type' => PurchaseInvoice::class,
                        'reference_id' => $invoice->id,
                        'description' => "قيد عكسي - إلغاء فاتورة مشتريات رقم {$invoice->invoice_number}",
                        'status' => 'posted',
                        'posted_by' => auth()->id(),
                        'posted_at' => now(),
                    ]);

                    foreach ($originalJe->lines as $line) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $reversalJe->id,
                            'account_id' => $line->account_id,
                            'debit' => $line->credit,
                            'credit' => $line->debit,
                            'description' => "عكس قيد - " . ($line->description ?? "فاتورة مشتريات رقم {$invoice->invoice_number}"),
                        ]);
                    }
                }
            }

            // 3. Update status and due amount
            $invoice->update([
                'status' => 'cancelled',
                'due_amount' => 0,
            ]);

            ActivityLog::log('purchase_invoice_cancelled', $invoice, "Cancelled purchase invoice {$invoice->invoice_number}");
        });

        return redirect()->back()->with('success', 'تم إلغاء فاتورة المشتريات وخصم الكميات الصادرة وتوليد القيد المحاسبي العكسي بنجاح.');
    }
}

