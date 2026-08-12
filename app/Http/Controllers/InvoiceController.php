<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\StockMovement;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ActivityLog;
use App\Services\AccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\StoreInvoiceRequest;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class InvoiceController extends Controller
{
    use AuthorizesRequests;

    public function index($locale = 'ar', Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('view-invoices');

        $query = Invoice::with(['customer', 'branch', 'warehouse']);

        $user = auth()->user();
        if ($user && !$user->hasRole(['system-admin', 'general-manager']) && $user->main_branch_id) {
            $query->where('branch_id', $user->main_branch_id);
        }

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
        $items = InventoryItem::with(['baseUnit', 'wholesaleUnit', 'warehouseItems'])->where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $categories = ItemCategory::where('is_active', true)->get();

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

        return view('sales.invoices.create', compact('customers', 'warehouses', 'items', 'units', 'categories', 'cashAccounts', 'bankAccounts'));
    }

    public function store($locale = 'ar', StoreInvoiceRequest $request = null)
    {
        $request = $request ?? request();
        $this->authorize('create-invoices');

        // Validate that issue date is not within a closed financial period
        try {
            app(\App\Services\AccountingService::class)->validatePeriodNotLocked($request->input('issue_date', date('Y-m-d')));
        } catch (\InvalidArgumentException $e) {
            return redirect()->back()->withInput()->with('error', $e->getMessage());
        }

        // Pre-validate stock availability for all items in the chosen warehouse
        $itemBaseQtyTotals = [];
        foreach ($request->items as $row) {
            $item = InventoryItem::find($row['inventory_item_id']);
            $unit = Unit::find($row['unit_id']);
            if (!$item || !$unit) continue;

            $conversion = ($item->wholesale_unit_id == $unit->id) ? (float)$item->conversion_factor : 1.0;
            $qtyInBase = (float)$row['quantity'] * $conversion;
            $itemBaseQtyTotals[$item->id] = ($itemBaseQtyTotals[$item->id] ?? 0) + $qtyInBase;
        }

        DB::beginTransaction();
        try {
            foreach ($itemBaseQtyTotals as $itemId => $totalRequiredBase) {
                $invItem = InventoryItem::find($itemId);
                $whItem = WarehouseItem::where('warehouse_id', $request->warehouse_id)
                    ->where('inventory_item_id', $itemId)
                    ->lockForUpdate()
                    ->first();

                $availableStock = $whItem ? max(0, (float)$whItem->qty_in_base_units) : 0;
                if ($totalRequiredBase > $availableStock) {
                    DB::rollBack();
                    $unitName = $invItem->baseUnit?->name ?? 'قطعة';
                    return redirect()->back()->withInput()->with('error', "عفواً، الكمية المطلوبة من الصنف ({$invItem->name}) وهي ({$totalRequiredBase} {$unitName}) تتجاوز الرصيد المتوفر بالمخزن ({$availableStock} {$unitName}).");
                }
            }

            $maxId = (int) DB::table('invoices')->max('id');

            $invoiceNumber = 'INV-' . date('Ymd') . '-' . str_pad($maxId + 1, 4, '0', STR_PAD_LEFT);
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
                'status' => 'draft',
                'notes' => $request->notes,
                'subtotal' => 0,
                'tax_amount' => 0,
                'total_amount' => 0,
            ]);

            foreach ($request->items as $row) {
                $item = InventoryItem::find($row['inventory_item_id']);
                $unit = Unit::find($row['unit_id']);

                // Calculate base quantity
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
                    'unit_of_measure' => $unit?->name ?? 'piece',
                    'tax_percent' => $taxRate,
                    'tax_amount' => $lineTax,
                    'subtotal' => $lineSubtotal,
                    'total' => $lineTotal,
                ]);

                // Deduct stock safely from destination warehouse with row lock
                $whItem = WarehouseItem::where('warehouse_id', $request->warehouse_id)
                    ->where('inventory_item_id', $item->id)
                    ->lockForUpdate()
                    ->first();

                if (!$whItem) {
                    $whItem = WarehouseItem::create([
                        'warehouse_id' => $request->warehouse_id,
                        'inventory_item_id' => $item->id,
                        'qty_in_base_units' => 0,
                    ]);
                }

                $newQty = max(0, (float)$whItem->qty_in_base_units - $qtyInBase);
                $whItem->update(['qty_in_base_units' => $newQty]);


                // Record stock movement for sale
                StockMovement::create([
                    'warehouse_id' => $request->warehouse_id,
                    'item_id' => $item->id,
                    'movement_type' => 'out',
                    'quantity' => $qtyInBase,
                    'reference_type' => Invoice::class,
                    'reference_id' => $invoice->id,
                    'notes' => "فاتورة مبيعات رقم {$invoice->invoice_number}",
                    'created_by' => auth()->id(),
                ]);
            }

            $totalAmount = $subtotal + $totalTax;

            // Calculate Split Payments (Cash, Bank, Due)
            $cashAmount = 0.0;
            $bankAmount = 0.0;
            $dueAmount = 0.0;
            $cashAccountId = $request->cash_account_id;
            $bankAccountId = $request->bank_account_id;

            if ($request->payment_type === 'cash') {
                $cashAmount = $totalAmount;
                if (!$cashAccountId) {
                    $cashAccountId = AccountResolver::getCashboxAccount()?->id;
                }
            } elseif ($request->payment_type === 'bank') {
                $bankAmount = $totalAmount;
                if (!$bankAccountId) {
                    $bankAccountId = AccountResolver::getBankAccount()?->id;
                }
            }
 elseif ($request->payment_type === 'credit') {
                $dueAmount = $totalAmount;
            } elseif ($request->payment_type === 'split') {
                $cashAmount = min($totalAmount, max(0, (float)$request->cash_amount));
                $bankAmount = min($totalAmount - $cashAmount, max(0, (float)$request->bank_amount));
                $dueAmount = max(0, $totalAmount - $cashAmount - $bankAmount);
            }

            if ($dueAmount > 0) {
                $customer = Customer::find($request->customer_id);
                if ($customer && $customer->credit_limit > 0) {
                    $existingDue = (float) Invoice::where('customer_id', $customer->id)
                        ->whereNotIn('status', ['paid', 'cancelled'])
                        ->sum('due_amount');
                    if (($existingDue + $dueAmount) > $customer->credit_limit) {
                        $availableCredit = max(0, $customer->credit_limit - $existingDue);
                        throw new \InvalidArgumentException("عفواً، مديونية العميل الحالية والجديدة متجاوزة للحد الائتماني المسموح به ({$customer->credit_limit} ر.س). المتاح للبيع الآجل حالياً: {$availableCredit} ر.س.");
                    }
                }
            }

            $status = 'issued';
            if ($dueAmount <= 0.001) {
                $status = 'paid';
            } elseif (($cashAmount + $bankAmount) > 0) {
                $status = 'partially_paid';
            }

            $invoice->update([
                'subtotal' => $subtotal,
                'tax_amount' => $totalTax,
                'total_amount' => $totalAmount,
                'cash_amount' => $cashAmount,
                'bank_amount' => $bankAmount,
                'due_amount' => $dueAmount,
                'cash_account_id' => $cashAccountId,
                'bank_account_id' => $bankAccountId,
                'status' => $status,
            ]);

            // Automatic Multi-Line Journal Entry on Chart of Accounts
            $salesAccount = AccountResolver::getSalesAccount();
            $vatAccount = AccountResolver::getVatAccount();
            $customerModel = Customer::find($request->customer_id);
            $arAccount = AccountResolver::getCustomerAccount($customerModel);

            if ($salesAccount) {

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

                // 1. Debit Cash Account if cash paid
                if ($cashAmount > 0) {
                    $cAccId = $cashAccountId ?: AccountResolver::getCashboxAccount()?->id;
                    if ($cAccId) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $je->id,
                            'account_id' => $cAccId,
                            'debit' => $cashAmount,
                            'credit' => 0,
                            'description' => "سداد نقدي فاتورة مبيعات {$invoice->invoice_number}",
                        ]);
                    }
                }

                // 2. Debit Bank Account if bank paid
                if ($bankAmount > 0) {
                    $bAccId = $bankAccountId ?: AccountResolver::getBankAccount()?->id;

                    if ($bAccId) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $je->id,
                            'account_id' => $bAccId,
                            'debit' => $bankAmount,
                            'credit' => 0,
                            'description' => "سداد بنكي فاتورة مبيعات {$invoice->invoice_number}",
                        ]);
                    }
                }

                // 3. Debit Customer (AR) if due amount
                if ($dueAmount > 0 && $arAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $arAccount->id,
                        'debit' => $dueAmount,
                        'credit' => 0,
                        'description' => "مستحق على العميل فاتورة مبيعات {$invoice->invoice_number}",
                    ]);
                }

                // 4. Credit Sales Revenue
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $salesAccount->id,
                    'debit' => 0,
                    'credit' => $subtotal,
                    'description' => "إيراد مبيعات فاتورة {$invoice->invoice_number}",
                ]);

                // 5. Credit VAT
                if ($totalTax > 0 && $vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $vatAccount->id,
                        'debit' => 0,
                        'credit' => $totalTax,
                        'description' => "ضريبة قيمة مضافة ({$taxRate}%)",
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

    public function updateStatus(Request $request, $locale = 'ar', $invoice = null)
    {
        $targetId = ($invoice instanceof Invoice) ? $invoice->id : ($invoice ?? request()->route('invoice'));
        $invoiceModel = ($targetId instanceof Invoice) ? $targetId : Invoice::findOrFail($targetId);
        $invoice = $invoiceModel;



        $request->validate([
            'status' => 'required|in:draft,issued,paid,partially_paid,cancelled',
        ]);

        $newStatus = $request->status;

        if ($newStatus === 'cancelled') {
            if ($invoice->status === 'cancelled') {
                return redirect()->back()->with('warning', 'فاتورة المبيعات ملغاة بالفعل مسبقاً.');
            }

            DB::transaction(function () use ($invoice) {
                // 1. Revert stock deduction (Increase warehouse item stock and record movement)
                $invoice->load(['items.item', 'warehouse']);
                foreach ($invoice->items as $invoiceItem) {
                    $item = $invoiceItem->item;
                    if (!$item) continue;

                    $qtyInBase = (float) (($invoiceItem->qty_in_base_units > 0) ? $invoiceItem->qty_in_base_units : $invoiceItem->quantity);


                    $whItem = WarehouseItem::firstOrCreate(
                        ['warehouse_id' => $invoice->warehouse_id, 'inventory_item_id' => $item->id],
                        ['qty_in_base_units' => 0]
                    );
                    $whItem->increment('qty_in_base_units', $qtyInBase);

                    StockMovement::create([
                        'warehouse_id' => $invoice->warehouse_id,
                        'item_id' => $item->id,
                        'movement_type' => 'in',
                        'quantity' => $qtyInBase,
                        'reference_type' => Invoice::class,
                        'reference_id' => $invoice->id,
                        'notes' => "عكس خصم مخزون - إلغاء فاتورة مبيعات رقم {$invoice->invoice_number}",
                        'created_by' => auth()->id(),
                    ]);
                }

                // 2. Reverse accounting journal entry (Storno)
                $originalJe = JournalEntry::where(function ($q) use ($invoice) {
                        $q->where(function ($sub) use ($invoice) {
                            $sub->where('reference_type', Invoice::class)
                               ->where('reference_id', $invoice->id);
                        })->orWhere('entry_number', 'JE-INV-' . $invoice->id);
                    })
                    ->where('status', 'posted')
                    ->first();

                if ($originalJe) {
                    $existingRev = JournalEntry::where('entry_number', 'JE-REV-INV-' . $invoice->id)->first();

                    if (!$existingRev) {
                        $reversalJe = JournalEntry::create([
                            'entry_number' => 'JE-REV-INV-' . $invoice->id,
                            'entry_date' => now()->toDateString(),
                            'reference_type' => Invoice::class,
                            'reference_id' => $invoice->id,
                            'description' => "قيد عكسي - إلغاء فاتورة مبيعات رقم {$invoice->invoice_number}",
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
                                'description' => "عكس قيد - " . ($line->description ?? "فاتورة مبيعات رقم {$invoice->invoice_number}"),
                            ]);
                        }
                    }
                }


                // 3. Update invoice status and due amount
                $invoice->update([
                    'status' => 'cancelled',
                    'due_amount' => 0,
                ]);

                ActivityLog::log('invoice_cancelled', $invoice, "Cancelled sales invoice {$invoice->invoice_number}");
            });

            return redirect()->back()->with('success', 'تم إلغاء فاتورة المبيعات وإعادة الكميات للمخزن وتوليد القيد المحاسبي العكسي بنجاح');
        }

        $invoice->update([
            'status' => $newStatus,
        ]);

        return redirect()->back()->with('success', 'تم تحديث حالة الفاتورة بنجاح');
    }

    public function batchPrint($locale = 'ar', Request $request = null)
    {
        $request = $request ?? request();
        $this->authorize('view-invoices');

        $ids = $request->input('ids', []);
        if (empty($ids) || !is_array($ids)) {
            return back()->with('error', 'يرجى تحديد فاتورة واحدة على الأقل للطباعة الجماعية.');
        }

        $invoices = Invoice::with(['customer', 'branch', 'items.inventoryItem', 'items.unit'])
            ->whereIn('id', $ids)
            ->latest('issue_date')
            ->get();

        return view('sales.invoices.batch-print', compact('invoices'));
    }
}

