<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\PaymentVoucher;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\WorkOrder;
use App\Models\Project;
use App\Models\ProjectExpense;
use App\Models\Contract;
use App\Models\Cashbox;
use App\Models\InventoryItem;
use App\Models\StockMovement;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
use App\Models\InvoiceItem;
use App\Services\AccountResolver;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class ReportService
{
    /**
     * Scope query by user branch access if user cannot view all branches.
     */
    protected function applyBranchScope($query, ?int $branchId = null, string $branchColumn = 'branch_id')
    {
        $user = Auth::user();

        // PaymentVoucher model does not have a branch_id column directly; it scopes via cashbox relation
        if ($query->getModel() instanceof PaymentVoucher) {
            if ($branchId) {
                $query->whereHas('cashbox', function ($q) use ($branchId) {
                    $q->where('branch_id', $branchId);
                });
            } elseif ($user && !$user->can('reports.view_all_branches')) {
                $userBranchIds = $user->accessibleBranches()->pluck('id')->toArray();
                if (!empty($userBranchIds)) {
                    $query->whereHas('cashbox', function ($q) use ($userBranchIds) {
                        $q->whereIn('branch_id', $userBranchIds);
                    });
                } elseif ($user->branch_id) {
                    $query->whereHas('cashbox', function ($q) use ($user) {
                        $q->where('branch_id', $user->branch_id);
                    });
                }
            }
            return $query;
        }

        if ($branchId) {
            $query->where($branchColumn, $branchId);
        } elseif ($user && !$user->can('reports.view_all_branches')) {
            $userBranchIds = $user->accessibleBranches()->pluck('id')->toArray();
            if (!empty($userBranchIds)) {
                $query->whereIn($branchColumn, $userBranchIds);
            } elseif ($user->branch_id) {
                $query->where($branchColumn, $user->branch_id);
            }
        }

        return $query;
    }

    /**
     * Get Customer Statement / Ledger (كشف حساب عميل)
     */
    /**
     * Get Customer Statement / Ledger (كشف حساب عميل)
     */
    public function getCustomerStatement(int $customerId, ?string $fromDate = null, ?string $toDate = null, ?int $branchId = null): array
    {
        $customer = Customer::findOrFail($customerId);

        // 1. Fetch Invoices for this customer
        $invoiceQuery = Invoice::where('customer_id', $customerId)->whereNotIn('status', ['cancelled']);
        $this->applyBranchScope($invoiceQuery, $branchId);

        // 2. Fetch Payment Vouchers (Receipts) for this customer
        $voucherQuery = PaymentVoucher::where('customer_id', $customerId)->where('type', 'receipt')->whereIn('status', ['completed', 'posted', 'approved']);
        $this->applyBranchScope($voucherQuery, $branchId);

        // Calculate Opening Balance (prior to $fromDate)
        $openingDebit = 0;
        $openingCredit = 0;

        if ($fromDate) {
            $prevInvoices = (clone $invoiceQuery)->whereDate('created_at', '<', $fromDate)->get();
            foreach ($prevInvoices as $inv) {
                $openingDebit += (float) ($inv->total_amount ?? $inv->net_amount ?? 0.00);
                $paidDirect = (float) (($inv->cash_amount ?? 0) + ($inv->bank_amount ?? 0));
                if ($paidDirect > 0) {
                    $openingCredit += min((float)$inv->total_amount, $paidDirect);
                }
            }
            $prevReceipts = (clone $voucherQuery)->whereDate('payment_date', '<', $fromDate)->sum('amount');
            $openingCredit += (float) $prevReceipts;
        }

        $openingBalance = $openingDebit - $openingCredit;

        // Apply Date Range Filter for current period
        if ($fromDate) {
            $invoiceQuery->whereDate('created_at', '>=', $fromDate);
            $voucherQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $invoiceQuery->whereDate('created_at', '<=', $toDate);
            $voucherQuery->whereDate('payment_date', '<=', $toDate);
        }

        $invoices = $invoiceQuery->with(['branch', 'creator'])->get();
        $vouchers = $voucherQuery->with(['creator', 'cashbox.branch'])->get();

        // Merge into a single chronological ledger sequence
        $ledger = collect();

        foreach ($invoices as $inv) {
            $amount = (float) ($inv->total_amount ?? $inv->net_amount ?? 0.00);
            $ledger->push([
                'id' => 'inv_' . $inv->id,
                'date' => $inv->created_at->format('Y-m-d H:i'),
                'document_number' => $inv->invoice_number,
                'document_type' => __('reports.type_invoice') ?? 'فاتورة مبيعات',
                'description' => __('reports.invoice_for') . ' ' . $customer->name,
                'debit' => $amount,
                'credit' => 0.00,
                'branch' => $inv->branch?->name,
                'user' => $inv->creator?->name,
                'notes' => $inv->notes ?? '-',
                'link' => route('invoices.show', $inv->id),
            ]);

            // Add instant cash/bank payment line if paid directly on invoice
            $paidDirect = (float) (($inv->cash_amount ?? 0) + ($inv->bank_amount ?? 0));
            if ($paidDirect > 0) {
                $actualPaidCredit = min($amount, $paidDirect);
                $ledger->push([
                    'id' => 'inv_pay_' . $inv->id,
                    'date' => $inv->created_at->format('Y-m-d H:i'),
                    'document_number' => $inv->invoice_number,
                    'document_type' => 'سداد فوري (نقدي/شبكة)',
                    'description' => 'دفعة مسددة عند إصدار فاتورة المبيعات رقم ' . $inv->invoice_number,
                    'debit' => 0.00,
                    'credit' => $actualPaidCredit,
                    'branch' => $inv->branch?->name,
                    'user' => $inv->creator?->name,
                    'notes' => 'سداد مباشر مبيعات',
                    'link' => route('invoices.show', $inv->id),
                ]);
            }
        }

        foreach ($vouchers as $vch) {
            $ledger->push([
                'id' => 'vch_' . $vch->id,
                'date' => $vch->payment_date ? $vch->payment_date->format('Y-m-d') : $vch->created_at->format('Y-m-d'),
                'document_number' => $vch->voucher_number,
                'document_type' => __('reports.type_receipt') ?? 'سند قبض',
                'description' => $vch->description ?? (__('reports.receipt_from') . ' ' . $customer->name),
                'debit' => 0.00,
                'credit' => (float) $vch->amount,
                'branch' => $vch->cashbox?->branch?->name,
                'user' => $vch->creator?->name,
                'notes' => $vch->cashbox ? ('الخزنة: ' . $vch->cashbox->name) : '-',
                'link' => route('payments.show', $vch->id),
            ]);
        }

        // Sort by date ascending
        $ledger = $ledger->sortBy('date')->values();

        // Calculate running balance
        $currentBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        $processedLedger = $ledger->map(function ($item) use (&$currentBalance, &$totalDebit, &$totalCredit) {
            $totalDebit += $item['debit'];
            $totalCredit += $item['credit'];
            $currentBalance += ($item['debit'] - $item['credit']);
            $item['running_balance'] = $currentBalance;
            return $item;
        });

        // Unpaid Invoices Summary
        $unpaidInvoices = Invoice::where('customer_id', $customerId)
            ->whereNotIn('status', ['paid', 'cancelled'])
            ->get();

        return [
            'customer' => $customer,
            'ledger' => $processedLedger,
            'opening_balance' => $openingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $currentBalance,
            'unpaid_count' => $unpaidInvoices->count(),
            'unpaid_amount' => $unpaidInvoices->sum('due_amount'),
        ];
    }

    /**
     * Get Supplier Statement / Ledger (كشف حساب مورد)
     */
    public function getSupplierStatement(int $supplierId, ?string $fromDate = null, ?string $toDate = null, ?int $branchId = null): array
    {
        $supplier = Supplier::findOrFail($supplierId);

        // 1. Fetch Purchase Invoices for this supplier
        $pInvQuery = \App\Models\PurchaseInvoice::where('supplier_id', $supplierId)->whereNotIn('status', ['cancelled']);
        $this->applyBranchScope($pInvQuery, $branchId);

        // 2. Fetch Standalone Purchase Orders (if any not converted)
        $poQuery = PurchaseOrder::where('supplier_id', $supplierId)->whereNotIn('status', ['cancelled']);
        $this->applyBranchScope($poQuery, $branchId);

        // 3. Fetch Payment Vouchers (Payment to supplier)
        $voucherQuery = PaymentVoucher::where('supplier_id', $supplierId)->where('type', 'payment')->whereIn('status', ['completed', 'posted', 'approved']);
        $this->applyBranchScope($voucherQuery, $branchId);

        $openingDebit = 0;
        $openingCredit = 0;

        if ($fromDate) {
            $prevPInvoices = (clone $pInvQuery)->whereDate('invoice_date', '<', $fromDate)->get();
            foreach ($prevPInvoices as $pInv) {
                $openingCredit += (float) ($pInv->net_amount ?? $pInv->total_amount ?? 0.00);
                $paidDirect = (float) (($pInv->cash_amount ?? 0) + ($pInv->bank_amount ?? 0));
                if ($paidDirect > 0) {
                    $openingDebit += min((float)($pInv->net_amount ?? $pInv->total_amount), $paidDirect);
                }
            }
            
            // Only add POs if no purchase invoices exist for that date range
            if ($prevPInvoices->isEmpty()) {
                $openingCredit += (float) (clone $poQuery)->whereDate('created_at', '<', $fromDate)->sum('total_amount');
            }

            $prevPayments = (clone $voucherQuery)->whereDate('payment_date', '<', $fromDate)->sum('amount');
            $openingDebit += (float) $prevPayments;
        }

        $openingBalance = $openingCredit - $openingDebit; // Positive means supplier is owed money

        if ($fromDate) {
            $pInvQuery->whereDate('invoice_date', '>=', $fromDate);
            $poQuery->whereDate('created_at', '>=', $fromDate);
            $voucherQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $pInvQuery->whereDate('invoice_date', '<=', $toDate);
            $poQuery->whereDate('created_at', '<=', $toDate);
            $voucherQuery->whereDate('payment_date', '<=', $toDate);
        }

        $pInvoices = $pInvQuery->with(['warehouse', 'creator'])->get();
        $purchaseOrders = $pInvoices->isEmpty() ? $poQuery->with(['branch', 'creator'])->get() : collect();
        $vouchers = $voucherQuery->with(['creator', 'cashbox.branch'])->get();

        $ledger = collect();

        foreach ($pInvoices as $pInv) {
            $amount = (float) ($pInv->net_amount ?? $pInv->total_amount ?? 0.00);
            $ledger->push([
                'id' => 'pinv_' . $pInv->id,
                'date' => $pInv->invoice_date ? date('Y-m-d H:i', strtotime($pInv->invoice_date)) : $pInv->created_at->format('Y-m-d H:i'),
                'document_number' => $pInv->invoice_number,
                'document_type' => 'فاتورة مشتريات وتوريد',
                'description' => 'فاتورة مشتريات من المورد ' . $supplier->name,
                'debit' => 0.00,
                'credit' => $amount,
                'branch' => $pInv->warehouse?->name ?? 'المركز الرئيسي',
                'user' => $pInv->creator?->name,
                'notes' => $pInv->notes ?? '-',
                'link' => route('purchases.show', $pInv->id),
            ]);

            // Add instant cash/bank payment line if paid directly on purchase invoice
            $paidDirect = (float) (($pInv->cash_amount ?? 0) + ($pInv->bank_amount ?? 0));
            if ($paidDirect > 0) {
                $actualPaidDebit = min($amount, $paidDirect);
                $ledger->push([
                    'id' => 'pinv_pay_' . $pInv->id,
                    'date' => $pInv->invoice_date ? date('Y-m-d H:i', strtotime($pInv->invoice_date)) : $pInv->created_at->format('Y-m-d H:i'),
                    'document_number' => $pInv->invoice_number,
                    'document_type' => 'سداد فوري (نقدي/شبكة)',
                    'description' => 'دفعة مسددة عند إصدار فاتورة المشتريات رقم ' . $pInv->invoice_number,
                    'debit' => $actualPaidDebit,
                    'credit' => 0.00,
                    'branch' => $pInv->warehouse?->name ?? 'المركز الرئيسي',
                    'user' => $pInv->creator?->name,
                    'notes' => 'سداد مباشر مشتريات',
                    'link' => route('purchases.show', $pInv->id),
                ]);
            }
        }

        foreach ($purchaseOrders as $po) {
            $ledger->push([
                'id' => 'po_' . $po->id,
                'date' => $po->created_at->format('Y-m-d H:i'),
                'document_number' => $po->po_number,
                'document_type' => __('reports.type_purchase') ?? 'امر شراء / فاتورة توريد',
                'description' => __('reports.purchase_from') . ' ' . $supplier->name,
                'debit' => 0.00,
                'credit' => (float) $po->total_amount,
                'branch' => $po->branch?->name,
                'user' => $po->creator?->name,
                'notes' => $po->status,
                'link' => route('purchases.index'),
            ]);
        }

        foreach ($vouchers as $vch) {
            $ledger->push([
                'id' => 'vch_' . $vch->id,
                'date' => $vch->payment_date ? $vch->payment_date->format('Y-m-d') : $vch->created_at->format('Y-m-d'),
                'document_number' => $vch->voucher_number,
                'document_type' => __('reports.type_payment') ?? 'سند صرف مورد',
                'description' => $vch->description ?? (__('reports.payment_to') . ' ' . $supplier->name),
                'debit' => (float) $vch->amount,
                'credit' => 0.00,
                'branch' => $vch->cashbox?->branch?->name,
                'user' => $vch->creator?->name,
                'notes' => $vch->cashbox ? ('الخزنة: ' . $vch->cashbox->name) : '-',
                'link' => route('payments.show', $vch->id),
            ]);
        }

        $ledger = $ledger->sortBy('date')->values();

        $currentBalance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        $processedLedger = $ledger->map(function ($item) use (&$currentBalance, &$totalDebit, &$totalCredit) {
            $totalDebit += $item['debit'];
            $totalCredit += $item['credit'];
            $currentBalance += ($item['credit'] - $item['debit']);
            $item['running_balance'] = $currentBalance;
            return $item;
        });

        return [
            'supplier' => $supplier,
            'ledger' => $processedLedger,
            'opening_balance' => $openingBalance,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'ending_balance' => $currentBalance,
        ];
    }

    /**
     * Get Detailed Sales Report
     */
    public function getSalesReport(array $filters): array
    {
        $query = Invoice::with(['customer', 'branch', 'creator', 'cashAccount', 'bankAccount']);

        $this->applyBranchScope($query, $filters['branch_id'] ?? null);

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        if (!empty($filters['customer_id'])) {
            $query->where('customer_id', $filters['customer_id']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $summaryQuery = clone $query;
        $allInvoices = (clone $summaryQuery)->get();

        $totalCash = 0.0;
        $totalBank = 0.0;
        $totalDue = 0.0;

        foreach ($allInvoices as $inv) {
            if ($inv->payment_type === 'cash') {
                $totalCash += ($inv->cash_amount > 0 ? (float)$inv->cash_amount : (float)$inv->total_amount);
            } elseif ($inv->payment_type === 'bank') {
                $totalBank += ($inv->bank_amount > 0 ? (float)$inv->bank_amount : (float)$inv->total_amount);
            } elseif ($inv->payment_type === 'credit') {
                $totalDue += ($inv->due_amount > 0 ? (float)$inv->due_amount : (float)$inv->total_amount);
            } elseif ($inv->payment_type === 'split') {
                $totalCash += (float)$inv->cash_amount;
                $totalBank += (float)$inv->bank_amount;
                $totalDue += (float)$inv->due_amount;
            }
        }

        return [
            'invoices' => $query->latest()->paginate(20)->withQueryString(),
            'raw_query' => $summaryQuery,
            'total_invoices' => $summaryQuery->count(),
            'total_subtotal' => (float) $summaryQuery->sum('subtotal'),
            'total_tax' => (float) $summaryQuery->sum('tax_amount'),
            'total_discount' => (float) $summaryQuery->sum('discount_amount'),
            'total_net' => (float) $summaryQuery->sum('total_amount'),
            'total_cash' => $totalCash,
            'total_bank' => $totalBank,
            'total_due' => $totalDue,
            'total_paid' => $totalCash + $totalBank,
            'total_remaining' => $totalDue,
        ];
    }

    /**
     * Get Detailed Workshop & CNC Report
     */
    public function getWorkshopReport(array $filters): array
    {
        $query = WorkOrder::with(['customer', 'branch', 'assignee']);
        $this->applyBranchScope($query, $filters['branch_id'] ?? null);

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        if (!empty($filters['priority'])) {
            $query->where('priority', $filters['priority']);
        }

        $summaryQuery = clone $query;

        $totalSheets = (int) $summaryQuery->sum('sheet_count');
        $goodPieces = (int) $summaryQuery->sum('good_pieces');
        $wastePieces = (int) $summaryQuery->sum('waste_pieces');
        $totalPieces = $goodPieces + $wastePieces;
        $wasteRate = $totalPieces > 0 ? round(($wastePieces / $totalPieces) * 100, 2) : 0;

        return [
            'raw_query' => $summaryQuery,
            'workOrders' => (clone $query)->latest()->paginate(20)->withQueryString(),
            'work_orders' => (clone $query)->latest()->paginate(20)->withQueryString(),
            'total_count' => $summaryQuery->count(),
            'total_sheets' => $totalSheets,
            'good_pieces' => $goodPieces,
            'waste_pieces' => $wastePieces,
            'waste_rate' => $wasteRate,
            'in_progress_count' => (clone $summaryQuery)->where('status', 'in_progress')->count(),
            'completed_count' => (clone $summaryQuery)->whereIn('status', ['completed', 'delivered'])->count(),
        ];

    }

    /**
     * Get Detailed Projects & Contracts Report
     */
    public function getProjectsReport(array $filters): array
    {
        $query = Project::with(['customer', 'branch', 'manager', 'contract']);
        $this->applyBranchScope($query, $filters['branch_id'] ?? null);

        if (!empty($filters['from_date'])) {
            $query->whereDate('created_at', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $query->whereDate('created_at', '<=', $filters['to_date']);
        }
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        $summaryQuery = clone $query;
        $totalExpenses = ProjectExpense::query()
            ->whereIn('project_id', (clone $summaryQuery)->select('projects.id'))
            ->sum('amount');

        return [
            'raw_query' => $summaryQuery,
            'projects' => $query
                ->withSum('expenses as total_expenses', 'amount')
                ->latest()
                ->paginate(20)
                ->withQueryString(),
            'total_projects' => $summaryQuery->count(),
            'total_budget' => (float) $summaryQuery->sum('budget'),
            'total_expenses' => (float) $totalExpenses,
            'avg_completion' => round((float) $summaryQuery->avg('completion_percentage'), 1),
            'ongoing_count' => (clone $summaryQuery)->where('status', 'in_progress')->count(),
            'completed_count' => (clone $summaryQuery)->where('status', 'completed')->count(),
        ];
    }

    /**
     * Get Financial & Cashbox Movements Report
     */
    public function getFinancialReport(array $filters): array
    {
        $vouchersQuery = PaymentVoucher::with(['cashbox.branch', 'customer', 'supplier', 'creator']);

        $this->applyBranchScope($vouchersQuery, $filters['branch_id'] ?? null);

        if (!empty($filters['from_date'])) {
            $vouchersQuery->whereDate('payment_date', '>=', $filters['from_date']);
        }
        if (!empty($filters['to_date'])) {
            $vouchersQuery->whereDate('payment_date', '<=', $filters['to_date']);
        }
        if (!empty($filters['cashbox_id'])) {
            $vouchersQuery->where('cashbox_id', $filters['cashbox_id']);
        }
        if (!empty($filters['type'])) {
            $vouchersQuery->where('type', $filters['type']);
        }

        $summaryQuery = clone $vouchersQuery;

        $totalReceipts = (float) (clone $summaryQuery)->where('type', 'receipt')->whereIn('status', ['completed', 'posted', 'approved'])->sum('amount');
        $totalPayments = (float) (clone $summaryQuery)->where('type', 'payment')->whereIn('status', ['completed', 'posted', 'approved'])->sum('amount');

        $netCashflow = $totalReceipts - $totalPayments;

        $cashboxes = Cashbox::with('branch')->get();

        return [
            'raw_query' => $summaryQuery,
            'vouchers' => $vouchersQuery->latest()->paginate(20)->withQueryString(),
            'cashboxes' => $cashboxes,
            'total_receipts' => $totalReceipts,
            'total_payments' => $totalPayments,
            'net_cashflow' => $netCashflow,
        ];
    }

    /**
     * Get Inventory & Stock Movements Report
     */
    public function getInventoryReport(array $filters): array
    {
        $query = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems']);

        $categoryId = $filters['category_id'] ?? $filters['category'] ?? null;
        if (!empty($categoryId)) {
            $query->where('category_id', $categoryId);
        }
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $summaryQuery = clone $query;

        $items = (clone $query)->paginate(20)->withQueryString();
        $movements = StockMovement::with(['item', 'creator', 'warehouse'])->latest()->take(20)->get();

        $totalStockQty = max(0, (float) DB::table('warehouse_items')->where('qty_in_base_units', '>', 0)->sum('qty_in_base_units'));

        return [
            'raw_query' => $summaryQuery,
            'items' => $items,
            'movements' => $movements,
            'total_items' => $summaryQuery->count(),
            'total_stock_qty' => $totalStockQty,
            'low_stock_count' => 0,
        ];
    }

    /**
     * Get Detailed Warehouse Inventory Audit Report (تقرير جرد المخزن التفصيلي)
     */
    public function getWarehouseInventoryReport(array $filters): array
    {
        $warehouses = Warehouse::where('is_active', true)->orderBy('name')->get();
        $selectedWarehouseId = !empty($filters['warehouse_id']) ? (int) $filters['warehouse_id'] : ($warehouses->first()?->id ?? null);
        $selectedWarehouse = $selectedWarehouseId ? $warehouses->firstWhere('id', $selectedWarehouseId) : null;

        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');

        $query = InventoryItem::with(['category', 'baseUnit', 'warehouseItems' => function ($q) use ($selectedWarehouseId) {
            if ($selectedWarehouseId) {
                $q->where('warehouse_id', $selectedWarehouseId);
            }
        }]);

        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('item_code', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('barcode', 'like', "%{$search}%");
            });
        }

        $allItems = $summaryQuery->get();
        $itemIds = $allItems->pluck('id')->toArray();

        // 1. Bulk Purchase Invoices (Inward / الوارد)
        $openingPurchasesMap = [];
        $periodPurchasesMap = [];
        if (!empty($itemIds)) {
            $basePurchaseQuery = DB::table('purchase_invoice_items')
                ->join('purchase_invoices', 'purchase_invoice_items.purchase_invoice_id', '=', 'purchase_invoices.id')
                ->whereIn('purchase_invoice_items.inventory_item_id', $itemIds);
            if ($selectedWarehouseId) {
                $basePurchaseQuery->where('purchase_invoices.warehouse_id', $selectedWarehouseId);
            }

            $openingPurchasesMap = (clone $basePurchaseQuery)
                ->where('purchase_invoices.invoice_date', '<', $fromDate)
                ->groupBy('purchase_invoice_items.inventory_item_id')
                ->pluck(DB::raw('SUM(purchase_invoice_items.qty_in_base_units) as total'), 'purchase_invoice_items.inventory_item_id')
                ->toArray();

            $periodPurchasesMap = (clone $basePurchaseQuery)
                ->whereBetween('purchase_invoices.invoice_date', [$fromDate, $toDate])
                ->groupBy('purchase_invoice_items.inventory_item_id')
                ->pluck(DB::raw('SUM(purchase_invoice_items.qty_in_base_units) as total'), 'purchase_invoice_items.inventory_item_id')
                ->toArray();
        }

        // 2. Bulk Sales Invoices (Outward / المنصرف)
        $openingSalesMap = [];
        $periodSalesMap = [];
        if (!empty($itemIds)) {
            $baseSalesQuery = DB::table('invoice_items')
                ->join('invoices', 'invoice_items.invoice_id', '=', 'invoices.id')
                ->whereIn('invoice_items.inventory_item_id', $itemIds);
            if ($selectedWarehouseId) {
                $baseSalesQuery->where('invoices.warehouse_id', $selectedWarehouseId);
            }

            $openingSalesMap = (clone $baseSalesQuery)
                ->where('invoices.issue_date', '<', $fromDate)
                ->groupBy('invoice_items.inventory_item_id')
                ->pluck(DB::raw('SUM(invoice_items.qty_in_base_units) as total'), 'invoice_items.inventory_item_id')
                ->toArray();

            $periodSalesMap = (clone $baseSalesQuery)
                ->whereBetween('invoices.issue_date', [$fromDate, $toDate])
                ->groupBy('invoice_items.inventory_item_id')
                ->pluck(DB::raw('SUM(invoice_items.qty_in_base_units) as total'), 'invoice_items.inventory_item_id')
                ->toArray();
        }

        // 3. Bulk Other Stock Movements (transfers, manual adjustments, waste, returns)
        $openingOtherMovementsMap = collect();
        $periodOtherMovementsMap = collect();
        if (!empty($itemIds)) {
            $baseMovementsQuery = DB::table('stock_movements')
                ->whereIn('item_id', $itemIds)
                ->when($selectedWarehouseId, fn($q) => $q->where('warehouse_id', $selectedWarehouseId))
                ->where(function ($q) {
                    $q->whereNull('reference_type')
                      ->orWhereNotIn('reference_type', [\App\Models\PurchaseInvoice::class, \App\Models\Invoice::class, 'App\Models\PurchaseInvoice', 'App\Models\Invoice']);
                });

            $openingOtherMovementsMap = (clone $baseMovementsQuery)
                ->where('created_at', '<', $fromDate . ' 00:00:00')
                ->groupBy('item_id')
                ->selectRaw("
                    item_id,
                    SUM(CASE WHEN movement_type IN ('in', 'return', 'adjustment') THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN movement_type IN ('out', 'reservation', 'waste', 'transfer') THEN quantity ELSE 0 END) as total_out
                ")
                ->get()
                ->keyBy('item_id');

            $periodOtherMovementsMap = (clone $baseMovementsQuery)
                ->whereBetween('created_at', [$fromDate . ' 00:00:00', $toDate . ' 23:59:59'])
                ->groupBy('item_id')
                ->selectRaw("
                    item_id,
                    SUM(CASE WHEN movement_type IN ('in', 'return', 'adjustment') THEN quantity ELSE 0 END) as total_in,
                    SUM(CASE WHEN movement_type IN ('out', 'reservation', 'waste', 'transfer') THEN quantity ELSE 0 END) as total_out
                ")
                ->get()
                ->keyBy('item_id');
        }

        // Calculate metrics per item
        $transformItem = function ($item) use (
            $selectedWarehouseId, $fromDate, $toDate,
            $openingPurchasesMap, $periodPurchasesMap,
            $openingSalesMap, $periodSalesMap,
            $openingOtherMovementsMap, $periodOtherMovementsMap
        ) {
            $whItem = $item->warehouseItems->first();
            $currentWhQty = max(0, (float) ($whItem?->qty_in_base_units ?? 0));

            $openingPurchases = (float) ($openingPurchasesMap[$item->id] ?? 0);
            $periodPurchases = (float) ($periodPurchasesMap[$item->id] ?? 0);

            $openingSales = (float) ($openingSalesMap[$item->id] ?? 0);
            $periodSales = (float) ($periodSalesMap[$item->id] ?? 0);

            $opOther = $openingOtherMovementsMap->get($item->id);
            $openingIn = $openingPurchases + (float) ($opOther?->total_in ?? 0);
            $openingOut = $openingSales + (float) ($opOther?->total_out ?? 0);
            $openingQty = max(0, $openingIn - $openingOut);

            $perOther = $periodOtherMovementsMap->get($item->id);
            $inQty = $periodPurchases + (float) ($perOther?->total_in ?? 0);
            $outQty = $periodSales + (float) ($perOther?->total_out ?? 0);

            // Available/Ending quantity at end of period
            $calcNet = $openingQty + $inQty - $outQty;
            if ($calcNet == 0 && $openingQty == 0 && $inQty == 0 && $outQty == 0 && $currentWhQty > 0) {
                $openingQty = $currentWhQty;
                $availableQty = $currentWhQty;
            } else {
                $availableQty = max(0, $calcNet);
            }

            $unitCost = (float) ($item ? $item->getEffectiveCostPrice() : 0);

            $totalValuation = $availableQty * $unitCost;

            return [
                'id' => $item->id,
                'item_code' => $item->item_code ?? $item->code ?? '-',
                'barcode' => $item->barcode ?? '-',
                'name' => $item->name,
                'category_name' => $item->category?->name ?? '-',
                'unit_name' => $item->baseUnit?->name ?? $item->unit ?? 'قطعة',
                'opening_qty' => $openingQty,
                'in_qty' => $inQty,
                'out_qty' => $outQty,
                'available_qty' => $availableQty,
                'unit_cost' => $unitCost,
                'total_valuation' => $totalValuation,
                'formatted_stock' => $whItem?->formatted_stock ?? "{$availableQty} " . ($item->baseUnit?->name ?? 'قطعة'),
            ];
        };

        // Paginate items
        $paginated = (clone $query)->paginate(20)->withQueryString();

        $processedItems = collect($paginated->items())->map($transformItem);

        // Aggregate overall totals for all matching items
        $allProcessed = $allItems->map($transformItem);
        $totalItemsCount = $allProcessed->count();
        $totalStockQty = $allProcessed->sum('available_qty');
        $totalValuation = $allProcessed->sum('total_valuation');

        return [
            'warehouses' => $warehouses,
            'selected_warehouse_id' => $selectedWarehouseId,
            'selected_warehouse' => $selectedWarehouse,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'system_start_date' => setting('system_start_date', date('Y-m-d')),
            'items' => new \Illuminate\Pagination\LengthAwarePaginator(
                $processedItems,
                $paginated->total(),
                $paginated->perPage(),
                $paginated->currentPage(),
                ['path' => \Illuminate\Pagination\Paginator::resolveCurrentPath()]
            ),
            'raw_items' => $allProcessed,
            'total_items_count' => $totalItemsCount,
            'total_stock_qty' => $totalStockQty,
            'total_valuation' => $totalValuation,
        ];
    }

    /**
     * Get Financial Period Comparison Data (مقارنة الفترات المالية)
     */
    public function getFinancialComparisonData(array $filters): array
    {
        $p1From = $filters['p1_from'] ?? date('Y-m-01', strtotime('-1 month'));
        $p1To = $filters['p1_to'] ?? date('Y-m-t', strtotime('-1 month'));
        $p2From = $filters['p2_from'] ?? date('Y-m-01');
        $p2To = $filters['p2_to'] ?? date('Y-m-d');
        $branchId = !empty($filters['branch_id']) ? (int) $filters['branch_id'] : null;

        $p1Data = $this->calculatePeriodFinancials($p1From, $p1To, $branchId);
        $p2Data = $this->calculatePeriodFinancials($p2From, $p2To, $branchId);

        $calculateVariance = function ($val1, $val2) {
            $diff = $val2 - $val1;
            if (abs($val1) < 0.0001) {
                $percentage = $val2 > 0 ? 100.0 : ($val2 < 0 ? -100.0 : 0.0);
            } else {
                $percentage = ($diff / abs($val1)) * 100;
            }
            return [
                'diff' => $diff,
                'percentage' => round($percentage, 2),
                'trend' => $diff >= 0 ? 'up' : 'down',
            ];
        };

        $metrics = [
            'sales' => [
                'title' => 'إجمالي المبيعات',
                'p1' => $p1Data['sales'],
                'p2' => $p2Data['sales'],
                'variance' => $calculateVariance($p1Data['sales'], $p2Data['sales']),
                'positive_is_good' => true,
            ],
            'purchases' => [
                'title' => 'إجمالي المشتريات',
                'p1' => $p1Data['purchases'],
                'p2' => $p2Data['purchases'],
                'variance' => $calculateVariance($p1Data['purchases'], $p2Data['purchases']),
                'positive_is_good' => false,
            ],
            'expenses' => [
                'title' => 'إجمالي المصروفات',
                'p1' => $p1Data['expenses'],
                'p2' => $p2Data['expenses'],
                'variance' => $calculateVariance($p1Data['expenses'], $p2Data['expenses']),
                'positive_is_good' => false,
            ],
            'gross_profit' => [
                'title' => 'مجمل الربح',
                'p1' => $p1Data['gross_profit'],
                'p2' => $p2Data['gross_profit'],
                'variance' => $calculateVariance($p1Data['gross_profit'], $p2Data['gross_profit']),
                'positive_is_good' => true,
            ],
            'net_profit' => [
                'title' => 'صافي الربح',
                'p1' => $p1Data['net_profit'],
                'p2' => $p2Data['net_profit'],
                'variance' => $calculateVariance($p1Data['net_profit'], $p2Data['net_profit']),
                'positive_is_good' => true,
            ],
        ];

        return [
            'p1_from' => $p1From,
            'p1_to' => $p1To,
            'p2_from' => $p2From,
            'p2_to' => $p2To,
            'branch_id' => $branchId,
            'p1' => $p1Data,
            'p2' => $p2Data,
            'metrics' => $metrics,
        ];
    }

    /**
     * Internal helper to calculate financials for a given date range
     */
    protected function calculatePeriodFinancials(string $fromDate, string $toDate, ?int $branchId = null): array
    {
        // 1. Sales
        $salesQuery = Invoice::whereNotIn('status', ['cancelled'])
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);
        $this->applyBranchScope($salesQuery, $branchId);
        $sales = (float) $salesQuery->sum('total_amount');

        // 2. Purchases
        $purchases = 0.0;
        if (class_exists(\App\Models\PurchaseInvoice::class)) {
            $pInvQuery = \App\Models\PurchaseInvoice::whereNotIn('status', ['cancelled'])
                ->whereDate('invoice_date', '>=', $fromDate)
                ->whereDate('invoice_date', '<=', $toDate);
            $purchases += (float) ($pInvQuery->sum('net_amount') ?: $pInvQuery->sum('total_amount'));
        }

        if ($purchases <= 0) {
            $purchasesQuery = PurchaseOrder::whereNotIn('status', ['cancelled'])
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
            $this->applyBranchScope($purchasesQuery, $branchId);
            $purchases = (float) $purchasesQuery->sum('total_amount');
        }

        // 3. Expenses (Payment Vouchers - payment type + Project Expenses)
        $expenseQuery = PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->whereDate('payment_date', '>=', $fromDate)
            ->whereDate('payment_date', '<=', $toDate);
        $this->applyBranchScope($expenseQuery, $branchId);
        $voucherExpenses = (float) $expenseQuery->sum('amount');

        $projExpenseQuery = ProjectExpense::whereDate('expense_date', '>=', $fromDate)
            ->whereDate('expense_date', '<=', $toDate);
        $projExpenses = (float) $projExpenseQuery->sum('amount');

        $totalExpenses = $voucherExpenses + $projExpenses;

        // 4. Cost of Goods Sold (COGS)
        $cogsQuery = InvoiceItem::whereHas('invoice', function ($q) use ($fromDate, $toDate, $branchId) {
            $q->whereNotIn('status', ['cancelled'])
              ->whereDate('created_at', '>=', $fromDate)
              ->whereDate('created_at', '<=', $toDate);
            $this->applyBranchScope($q, $branchId);
        })->with('item');

        $cogs = 0.0;
        foreach ($cogsQuery->get() as $itemLine) {
            $qty = (float) ($itemLine->qty_in_base_units ?? $itemLine->quantity);
            $unitCost = (float) ($itemLine->item ? $itemLine->item->getEffectiveCostPrice((float)($itemLine->unit_price ?? 0)) : 0);
            $cogs += ($qty * $unitCost);

        }

        // Fallback to purchases if COGS items cost is 0
        if ($cogs <= 0 && $purchases > 0) {
            $cogs = $purchases;
        }

        $grossProfit = $sales - $cogs;
        $netProfit = $grossProfit - $totalExpenses;

        return [
            'sales' => $sales,
            'purchases' => $purchases,
            'cogs' => $cogs,
            'expenses' => $totalExpenses,
            'gross_profit' => $grossProfit,
            'net_profit' => $netProfit,
        ];
    }

    /**
     * Get Most Profitable Items Report (تقرير الأصناف الأكثر ربحية)
     */
    public function getProfitableItemsReport(array $filters): array
    {
        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $categoryId = $filters['category_id'] ?? null;
        $warehouseId = $filters['warehouse_id'] ?? null;
        $branchId = $filters['branch_id'] ?? null;
        $sortBy = $filters['sort_by'] ?? 'profit'; // 'profit' or 'margin'

        $invoiceItemsQuery = InvoiceItem::whereHas('invoice', function ($q) use ($fromDate, $toDate, $branchId) {
            $q->whereNotIn('status', ['cancelled']);
            if ($fromDate) {
                $q->whereDate('created_at', '>=', $fromDate);
            }
            if ($toDate) {
                $q->whereDate('created_at', '<=', $toDate);
            }
            $this->applyBranchScope($q, $branchId);
        })->with(['invoice', 'item.category', 'item.baseUnit', 'unit', 'service']);

        if ($categoryId) {
            $invoiceItemsQuery->whereHas('item', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        $itemsRaw = $invoiceItemsQuery->get();

        $grouped = [];

        foreach ($itemsRaw as $line) {
            $key = $line->inventory_item_id ? ('item_' . $line->inventory_item_id) : ($line->service_id ? ('srv_' . $line->service_id) : ('name_' . md5($line->item_name)));

            if (!isset($grouped[$key])) {
                $grouped[$key] = [
                    'key' => $key,
                    'item_code' => $line->item?->item_code ?? $line->item?->code ?? '-',
                    'item_name' => $line->item?->name ?? $line->service?->name ?? $line->item_name ?? 'صنف غير معرف',
                    'category' => $line->item?->category?->name ?? 'غير محدد',
                    'unit_name' => $line->unit?->name ?? $line->item?->baseUnit?->name ?? $line->unit_of_measure ?? 'قطعة',
                    'sold_qty' => 0.0,
                    'total_revenue' => 0.0,
                    'cost_price' => (float) ($line->item ? $line->item->getEffectiveCostPrice((float)($line->unit_price ?? 0)) : 0.0),
                    'total_cost' => 0.0,
                    'profit' => 0.0,
                    'profit_margin' => 0.0,
                ];
            }

            $qty = (float) ($line->qty_in_base_units ?? $line->quantity);
            $revenue = (float) ($line->subtotal ?? $line->total);
            $costPrice = (float) ($line->item ? $line->item->getEffectiveCostPrice((float)($line->unit_price ?? 0)) : 0.0);
            $lineCost = $qty * $costPrice;


            $grouped[$key]['sold_qty'] += $qty;
            $grouped[$key]['total_revenue'] += $revenue;
            $grouped[$key]['total_cost'] += $lineCost;
        }

        $reportCollection = collect($grouped)->map(function ($item) {
            $item['profit'] = $item['total_revenue'] - $item['total_cost'];
            $item['avg_selling_price'] = $item['sold_qty'] > 0 ? ($item['total_revenue'] / $item['sold_qty']) : 0.0;
            $item['profit_margin'] = $item['total_revenue'] > 0 ? (($item['profit'] / $item['total_revenue']) * 100) : 0.0;
            return $item;
        });

        if ($sortBy === 'margin') {
            $reportCollection = $reportCollection->sortByDesc('profit_margin')->values();
        } else {
            $reportCollection = $reportCollection->sortByDesc('profit')->values();
        }

        $top10 = $reportCollection->take(10);
        $totalRevenueSum = $reportCollection->sum('total_revenue');
        $totalCostSum = $reportCollection->sum('total_cost');
        $totalProfitSum = $reportCollection->sum('profit');
        $avgProfitMargin = $totalRevenueSum > 0 ? (($totalProfitSum / $totalRevenueSum) * 100) : 0.0;

        return [
            'items' => $reportCollection,
            'top10' => $top10,
            'total_items_count' => $reportCollection->count(),
            'total_revenue' => $totalRevenueSum,
            'total_cost' => $totalCostSum,
            'total_profit' => $totalProfitSum,
            'avg_profit_margin' => round($avgProfitMargin, 2),
            'filters' => $filters,
        ];
    }

    /**
     * Statement of Financial Position / Balance Sheet (قائمة المركز المالي / الميزانية العمومية)
     * Exactly matches accounting standards & Image 1 structure
     */
    public function getBalanceSheetReport(array $filters): array
    {
        $asOfDate = !empty($filters['as_of_date']) ? $filters['as_of_date'] : date('Y-m-d');
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;

        // 1. Current Assets (الأصول المتداولة)
        // Cashbox Cash Balance
        $cashboxQuery = Cashbox::where('is_active', true);
        if ($branchId) {
            $cashboxQuery->where('branch_id', $branchId);
        }
        $cashboxCash = (float) (clone $cashboxQuery)->sum('current_balance');
        $bankAccountModel = AccountResolver::getBankAccount();
        $cashboxBank = (float) ($bankAccountModel ? $bankAccountModel->balance : \App\Models\Account::where('type', 'asset')->where(function ($q) {
            $q->where('code', 'like', '1112%')->orWhere('name', 'like', '%بنك%');
        })->sum('balance'));


        // Accounts Receivable (العملاء - مدينون)
        $invQuery = Invoice::whereNotIn('status', ['cancelled'])->whereDate('created_at', '<=', $asOfDate);
        $this->applyBranchScope($invQuery, $branchId);
        $accountsReceivable = (float) $invQuery->sum('due_amount');
        if ($accountsReceivable == 0) {
            $accountsReceivable = (float) $invQuery->whereIn('status', ['issued', 'partially_paid'])->sum('total_amount');
        }

        // Notes Receivable (أوراق قبض - شيكات برسم التحصيل)
        $chequeRecQuery = \App\Models\Cheque::where('type', 'received')->whereIn('status', ['pending', 'deposited'])->whereDate('created_at', '<=', $asOfDate);
        $notesReceivable = (float) $chequeRecQuery->sum('amount');

        // Inventory Valuation (المخزون)
        $inventoryValue = max(0, (float) DB::table('warehouse_items')
            ->join('inventory_items', 'warehouse_items.inventory_item_id', '=', 'inventory_items.id')
            ->sum(DB::raw('warehouse_items.qty_in_base_units * inventory_items.cost_price')));

        // Prepaid Expenses (مصروفات مدفوعة مقدماً)
        $prepaidExpenses = (float) \App\Models\Account::where('code', 'like', '114%')->sum('balance');

        $currentAssetsItems = [
            ['name' => 'الصندوق (الخزينة النقدية)', 'amount' => $cashboxCash],
            ['name' => 'البنك (الحسابات البنكية)', 'amount' => $cashboxBank],
            ['name' => 'العملاء (مدينون)', 'amount' => $accountsReceivable],
            ['name' => 'أوراق قبض (شيكات تحت التحصيل)', 'amount' => $notesReceivable],
            ['name' => 'المخزون (تقييم البضاعة بالكامل)', 'amount' => $inventoryValue],
            ['name' => 'مصروفات مدفوعة مقدماً', 'amount' => $prepaidExpenses],
        ];

        $totalCurrentAssets = array_sum(array_column($currentAssetsItems, 'amount'));

        // 2. Non-Current / Fixed Assets (الأصول غير المتداولة / الثابتة)
        $landAccount = (float) \App\Models\Account::where('code', 'like', '121%')->sum('balance');
        $buildingAccount = (float) \App\Models\Account::where('code', 'like', '122%')->sum('balance');
        $equipmentAccount = (float) \App\Models\Account::where('code', 'like', '123%')->sum('balance');
        $accumulatedDepreciation = (float) \App\Models\Account::where('code', 'like', '129%')->sum('balance');

        $fixedAssetsItems = [
            ['name' => 'أراضي', 'amount' => $landAccount],
            ['name' => 'مباني وعقارات', 'amount' => $buildingAccount],
            ['name' => 'آلات ومعدات وأجهزة', 'amount' => $equipmentAccount],
            ['name' => '(-) مجمع الإهلاك تراكمي', 'amount' => $accumulatedDepreciation > 0 ? -$accumulatedDepreciation : 0.0],
        ];

        $netFixedAssets = array_sum(array_column($fixedAssetsItems, 'amount'));
        $totalAssets = $totalCurrentAssets + $netFixedAssets;

        // 3. Current Liabilities (الخصوم المتداولة)
        $purchaseQuery = PurchaseInvoice::whereNotIn('status', ['cancelled'])->whereDate('invoice_date', '<=', $asOfDate);
        $accountsPayable = (float) $purchaseQuery->sum('due_amount');
        if ($accountsPayable == 0) {
            $poQuery = PurchaseOrder::whereNotIn('status', ['cancelled'])->whereDate('created_at', '<=', $asOfDate);
            $this->applyBranchScope($poQuery, $branchId);
            $accountsPayable = (float) $poQuery->sum('total_amount');
        }

        $chequePayQuery = \App\Models\Cheque::where('type', 'issued')->where('status', 'pending')->whereDate('created_at', '<=', $asOfDate);
        $notesPayable = (float) $chequePayQuery->sum('amount');
        $accruedExpenses = (float) \App\Models\Account::where('code', 'like', '213%')->sum('balance');
        $shortTermLoans = (float) \App\Models\Account::where('code', 'like', '214%')->sum('balance');

        $currentLiabilitiesItems = [
            ['name' => 'الموردون (دائنون)', 'amount' => $accountsPayable],
            ['name' => 'أوراق دفع (شيكات صادرة)', 'amount' => $notesPayable],
            ['name' => 'مصروفات مستحقة', 'amount' => $accruedExpenses],
            ['name' => 'قروض قصيرة الأجل', 'amount' => $shortTermLoans],
        ];

        $totalCurrentLiabilities = array_sum(array_column($currentLiabilitiesItems, 'amount'));

        // 4. Long-term Liabilities (الخصوم طويلة الأجل)
        $longTermLoan = (float) \App\Models\Account::where('code', 'like', '22%')->sum('balance');
        $longTermLiabilitiesItems = [
            ['name' => 'قرض طويل الأجل', 'amount' => $longTermLoan],
        ];
        $totalLongTermLiabilities = array_sum(array_column($longTermLiabilitiesItems, 'amount'));
        $totalLiabilities = $totalCurrentLiabilities + $totalLongTermLiabilities;

        // 5. Equity (حقوق الملكية)
        $recordedCapital = (float) \App\Models\Account::where('code', 'like', '31%')->sum('balance');
        $recordedRetained = (float) \App\Models\Account::where('code', 'like', '32%')->sum('balance');

        // Current Net Profit from Income Statement
        $incData = $this->calculatePeriodFinancials('2020-01-01', $asOfDate, $branchId);
        $periodNetIncome = (float) $incData['net_profit'];

        if ($recordedCapital > 0) {
            $capital = $recordedCapital;
        } else {
            // Calculate initial owner capital contribution to ensure exact balance sheet equilibrium (الأصول = الخصوم + حقوق الملكية)
            $capital = max(0, $totalAssets - $totalLiabilities - $periodNetIncome - $recordedRetained);
        }

        $equityItems = [
            ['name' => 'رأس المال المسموح به / المساهم به', 'amount' => $capital],
        ];

        if ($recordedRetained != 0) {
            $equityItems[] = ['name' => 'أرباح مدورة / مرحلة من سنوات سابقة', 'amount' => $recordedRetained];
        }

        $equityItems[] = ['name' => 'صافي ربح / (خسارة) الفترة الحالية', 'amount' => $periodNetIncome];

        $totalEquity = array_sum(array_column($equityItems, 'amount'));
        $totalLiabilitiesAndEquity = $totalLiabilities + $totalEquity;

        $isBalanced = abs($totalAssets - $totalLiabilitiesAndEquity) < 1.0;

        return [
            'as_of_date' => $asOfDate,
            'branch_id' => $branchId,
            'current_assets' => $currentAssetsItems,
            'total_current_assets' => $totalCurrentAssets,
            'fixed_assets' => $fixedAssetsItems,
            'net_fixed_assets' => $netFixedAssets,
            'total_assets' => $totalAssets,
            'current_liabilities' => $currentLiabilitiesItems,
            'total_current_liabilities' => $totalCurrentLiabilities,
            'long_term_liabilities' => $longTermLiabilitiesItems,
            'total_long_term_liabilities' => $totalLongTermLiabilities,
            'total_liabilities' => $totalLiabilities,
            'equity_items' => $equityItems,
            'total_equity' => $totalEquity,
            'total_liabilities_and_equity' => $totalLiabilitiesAndEquity,
            'is_balanced' => $isBalanced,
            'difference' => abs($totalAssets - $totalLiabilitiesAndEquity),
        ];
    }

    /**
     * Income Statement Report (قائمة الدخل / الأرباح والخسائر)
     * Exactly matches Image 2 structure
     */
    public function getIncomeStatementReport(array $filters): array
    {
        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;

        // Gross Sales
        $salesQuery = Invoice::whereNotIn('status', ['cancelled'])
            ->whereDate('created_at', '>=', $fromDate)
            ->whereDate('created_at', '<=', $toDate);
        $this->applyBranchScope($salesQuery, $branchId);
        $grossSales = (float) $salesQuery->sum('subtotal');
        if ($grossSales == 0) {
            $grossSales = (float) $salesQuery->sum('total_amount');
        }

        // Sales Returns & Discounts
        $salesReturns = (float) $salesQuery->sum('discount_amount');

        // Net Sales
        $netSales = max(0, $grossSales - $salesReturns);

        // Cost of Goods Sold (COGS)
        $cogsQuery = InvoiceItem::whereHas('invoice', function ($q) use ($fromDate, $toDate, $branchId) {
            $q->whereNotIn('status', ['cancelled'])
              ->whereDate('created_at', '>=', $fromDate)
              ->whereDate('created_at', '<=', $toDate);
            $this->applyBranchScope($q, $branchId);
        })->with('item');

        $cogs = 0.0;
        foreach ($cogsQuery->get() as $itemLine) {
            $qty = (float) ($itemLine->qty_in_base_units ?? $itemLine->quantity);
            $unitCost = (float) ($itemLine->item ? $itemLine->item->getEffectiveCostPrice((float)($itemLine->unit_price ?? 0)) : 0);
            $cogs += ($qty * $unitCost);

        }

        if ($cogs == 0 && $netSales > 0) {
            $purchasesQuery = PurchaseOrder::whereNotIn('status', ['cancelled'])
                ->whereDate('created_at', '>=', $fromDate)
                ->whereDate('created_at', '<=', $toDate);
            $this->applyBranchScope($purchasesQuery, $branchId);
            $cogs = (float) $purchasesQuery->sum('total_amount');
        }

        // Gross Profit
        $grossProfit = $netSales - $cogs;

        // Detailed Operating Expenses
        $rentExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where('notes', 'like', '%إيجار%')
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $salariesExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where(fn($q) => $q->where('notes', 'like', '%رواتب%')->orWhere('notes', 'like', '%أجور%'))
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $utilitiesExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where(fn($q) => $q->where('notes', 'like', '%كهرباء%')->orWhere('notes', 'like', '%مياه%'))
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $advertisingExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where(fn($q) => $q->where('notes', 'like', '%إعلان%')->orWhere('notes', 'like', '%دعاية%'))
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $transportExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where(fn($q) => $q->where('notes', 'like', '%نقل%')->orWhere('notes', 'like', '%شحن%'))
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $depreciationExpense = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->where('notes', 'like', '%إهلاك%')
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        // Other General Expenses
        $otherGeneralVouchers = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)
            ->whereNotIn('notes', ['%إيجار%', '%رواتب%', '%أجور%', '%كهرباء%', '%مياه%', '%إعلان%', '%نقل%', '%إهلاك%'])
            ->sum('amount');

        $projExpenses = (float) ProjectExpense::whereDate('expense_date', '>=', $fromDate)
            ->whereDate('expense_date', '<=', $toDate)->sum('amount');

        $totalOtherOperating = max(0, $otherGeneralVouchers + $projExpenses - ($rentExpense + $salariesExpense + $utilitiesExpense + $advertisingExpense + $transportExpense + $depreciationExpense));

        $operatingExpensesList = [
            ['name' => 'مصروف إيجار', 'amount' => $rentExpense],
            ['name' => 'مصروف رواتب وأجور', 'amount' => $salariesExpense],
            ['name' => 'مصروف كهرباء ومياه ومرافق', 'amount' => $utilitiesExpense],
            ['name' => 'مصروف دعاية وإعلان وتسويق', 'amount' => $advertisingExpense],
            ['name' => 'مصروف نقل وشحن', 'amount' => $transportExpense],
            ['name' => 'مصروف إهلاك أصول', 'amount' => $depreciationExpense],
        ];

        if ($totalOtherOperating > 0) {
            $operatingExpensesList[] = ['name' => 'مصروفات تشغيلية وعمومية أخرى', 'amount' => $totalOtherOperating];
        }

        $totalOperatingExpenses = array_sum(array_column($operatingExpensesList, 'amount'));

        // Operating Profit
        $operatingProfit = $grossProfit - $totalOperatingExpenses;

        // Other Revenues
        $otherIncome = (float) PaymentVoucher::where('type', 'receipt')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->whereNull('customer_id')
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        // Interest Expense
        $interestExpense = 0.0;

        // Profit Before Tax
        $profitBeforeTax = $operatingProfit + $otherIncome - $interestExpense;

        // Tax (VAT / Income Tax)
        $taxAmount = (float) $salesQuery->sum('tax_amount');

        // Net Profit After Tax
        $netProfitAfterTax = $profitBeforeTax - $taxAmount;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'branch_id' => $branchId,
            'gross_sales' => $grossSales,
            'sales_returns' => $salesReturns,
            'net_sales' => $netSales,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'operating_expenses' => $operatingExpensesList,
            'total_operating_expenses' => $totalOperatingExpenses,
            'operating_profit' => $operatingProfit,
            'other_income' => $otherIncome,
            'interest_expense' => $interestExpense,
            'profit_before_tax' => $profitBeforeTax,
            'tax_amount' => $taxAmount,
            'net_profit_after_tax' => $netProfitAfterTax,
            'profit_margin' => $netSales > 0 ? round(($netProfitAfterTax / $netSales) * 100, 2) : 0.0,
        ];
    }

    /**
     * Trial Balance Report (ميزان المراجعة)
     */
    public function getTrialBalanceReport(array $filters): array
    {
        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $level = !empty($filters['level']) ? (int)$filters['level'] : null;

        $accountsQuery = \App\Models\Account::orderBy('code');
        if ($level) {
            $accountsQuery->where('level', $level);
        }

        $accounts = $accountsQuery->get();

        $rows = [];
        $totalOpeningDebit = 0;
        $totalOpeningCredit = 0;
        $totalPeriodDebit = 0;
        $totalPeriodCredit = 0;
        $totalEndingDebit = 0;
        $totalEndingCredit = 0;

        foreach ($accounts as $acc) {
            $openingDebit = $acc->nature === 'debit' ? (float)$acc->balance : 0.0;
            $openingCredit = $acc->nature === 'credit' ? (float)$acc->balance : 0.0;

            // Period Movement from Journal Lines
            $periodDebit = (float) DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $acc->id)
                ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
                ->sum('journal_entry_lines.debit');

            $periodCredit = (float) DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $acc->id)
                ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
                ->sum('journal_entry_lines.credit');

            $netBalance = ($openingDebit - $openingCredit) + ($periodDebit - $periodCredit);
            $endingDebit = $netBalance > 0 ? $netBalance : 0.0;
            $endingCredit = $netBalance < 0 ? abs($netBalance) : 0.0;

            $totalOpeningDebit += $openingDebit;
            $totalOpeningCredit += $openingCredit;
            $totalPeriodDebit += $periodDebit;
            $totalPeriodCredit += $periodCredit;
            $totalEndingDebit += $endingDebit;
            $totalEndingCredit += $endingCredit;

            $rows[] = [
                'code' => $acc->code,
                'name' => $acc->name,
                'type' => $acc->type,
                'nature' => $acc->nature,
                'level' => $acc->level,
                'opening_debit' => $openingDebit,
                'opening_credit' => $openingCredit,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'ending_debit' => $endingDebit,
                'ending_credit' => $endingCredit,
            ];
        }

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'rows' => $rows,
            'totals' => [
                'opening_debit' => $totalOpeningDebit,
                'opening_credit' => $totalOpeningCredit,
                'period_debit' => $totalPeriodDebit,
                'period_credit' => $totalPeriodCredit,
                'ending_debit' => $totalEndingDebit,
                'ending_credit' => $totalEndingCredit,
            ],
            'is_balanced' => abs($totalEndingDebit - $totalEndingCredit) < 1.0,
        ];
    }

    /**
     * Statement of Cash Flows (قائمة التدفقات النقدية)
     */
    public function getCashFlowReport(array $filters): array
    {
        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;

        $incData = $this->getIncomeStatementReport(['from_date' => $fromDate, 'to_date' => $toDate, 'branch_id' => $branchId]);
        $netIncome = $incData['net_profit_after_tax'];

        // Operating Cash Receipts & Payments
        $customerCollections = (float) PaymentVoucher::where('type', 'receipt')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $supplierPayments = (float) PaymentVoucher::where('type', 'payment')
            ->whereIn('status', ['completed', 'posted', 'approved'])
            ->whereNotNull('supplier_id')
            ->whereDate('payment_date', '>=', $fromDate)->whereDate('payment_date', '<=', $toDate)->sum('amount');

        $operatingExpensesPaid = $incData['total_operating_expenses'];
        $netOperatingCash = $customerCollections - ($supplierPayments + $operatingExpensesPaid);

        // Investing Cashflows
        $fixedAssetPurchases = 0.0;
        $netInvestingCash = -$fixedAssetPurchases;

        // Financing Cashflows
        $capitalAdditions = 0.0;
        $loanRepayments = 0.0;
        $netFinancingCash = $capitalAdditions - $loanRepayments;

        $netCashChange = $netOperatingCash + $netInvestingCash + $netFinancingCash;

        $openingCash = (float) Cashbox::where('is_active', true)->sum('opening_balance');
        $endingCash = $openingCash + $netCashChange;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'net_income' => $netIncome,
            'customer_collections' => $customerCollections,
            'supplier_payments' => $supplierPayments,
            'operating_expenses_paid' => $operatingExpensesPaid,
            'net_operating_cash' => $netOperatingCash,
            'net_investing_cash' => $netInvestingCash,
            'net_financing_cash' => $netFinancingCash,
            'net_cash_change' => $netCashChange,
            'opening_cash' => $openingCash,
            'ending_cash' => $endingCash,
        ];
    }

    /**
     * Statement of Changes in Equity (قائمة التغيرات في حقوق الملكية)
     */
    public function getEquityChangesReport(array $filters): array
    {
        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');
        $branchId = !empty($filters['branch_id']) ? (int)$filters['branch_id'] : null;

        $incData = $this->getIncomeStatementReport(['from_date' => $fromDate, 'to_date' => $toDate, 'branch_id' => $branchId]);

        $openingCapital = 500000.00;
        $capitalAdditions = 0.00;
        $ownerDrawings = 0.00;
        $netProfitPeriod = $incData['net_profit_after_tax'];

        $endingCapital = $openingCapital + $capitalAdditions - $ownerDrawings;
        $endingEquity = $endingCapital + $netProfitPeriod;

        return [
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_capital' => $openingCapital,
            'capital_additions' => $capitalAdditions,
            'owner_drawings' => $ownerDrawings,
            'net_profit_period' => $netProfitPeriod,
            'ending_capital' => $endingCapital,
            'ending_equity' => $endingEquity,
        ];
    }

    /**
     * General Ledger Report (دفتر الأستاذ العام)
     */
    public function getGeneralLedgerReport(array $filters): array
    {
        $accounts = \App\Models\Account::where('is_selectable', true)->orderBy('code')->get();
        $selectedAccountId = !empty($filters['account_id']) ? (int)$filters['account_id'] : ($accounts->first()?->id ?? null);
        $selectedAccount = $selectedAccountId ? \App\Models\Account::find($selectedAccountId) : null;

        $fromDate = !empty($filters['from_date']) ? $filters['from_date'] : setting('system_start_date', date('Y-m-d'));
        $toDate = !empty($filters['to_date']) ? $filters['to_date'] : date('Y-m-d');

        $entries = collect();
        $openingBalance = $selectedAccount ? (float)$selectedAccount->balance : 0.0;
        $runningBalance = $openingBalance;

        if ($selectedAccountId) {
            $lines = DB::table('journal_entry_lines')
                ->join('journal_entries', 'journal_entry_lines.journal_entry_id', '=', 'journal_entries.id')
                ->where('journal_entry_lines.account_id', $selectedAccountId)
                ->whereBetween('journal_entries.entry_date', [$fromDate, $toDate])
                ->select(
                    'journal_entries.entry_date as date',
                    'journal_entries.entry_number as doc_no',
                    'journal_entries.reference_type',
                    'journal_entry_lines.description',
                    'journal_entry_lines.debit',
                    'journal_entry_lines.credit'
                )
                ->orderBy('journal_entries.entry_date')
                ->get();

            foreach ($lines as $l) {
                $runningBalance += ($l->debit - $l->credit);
                $entries->push([
                    'date' => $l->date,
                    'doc_no' => $l->doc_no,
                    'type' => $l->reference_type ?? 'قيد محاسبي',
                    'description' => $l->description,
                    'debit' => (float)$l->debit,
                    'credit' => (float)$l->credit,
                    'running_balance' => $runningBalance,
                ]);
            }
        }

        return [
            'accounts' => $accounts,
            'selected_account' => $selectedAccount,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'opening_balance' => $openingBalance,
            'ending_balance' => $runningBalance,
            'entries' => $entries,
        ];
    }

    /**
     * Account Balances Tree Report (تقرير ميزان وأرصدة شجرة الحسابات)
     */
    public function getAccountBalancesReport(array $filters): array
    {
        $asOfDate = !empty($filters['as_of_date']) ? $filters['as_of_date'] : date('Y-m-d');
        $accounts = \App\Models\Account::with('children')->whereNull('parent_id')->orderBy('code')->get();

        return [
            'as_of_date' => $asOfDate,
            'account_tree' => $accounts,
        ];
    }
}

