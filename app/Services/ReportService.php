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
use App\Models\PurchaseOrder;
use App\Models\Branch;
use App\Models\User;
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
    public function getCustomerStatement(int $customerId, ?string $fromDate = null, ?string $toDate = null, ?int $branchId = null): array
    {
        $customer = Customer::findOrFail($customerId);

        // 1. Fetch Invoices for this customer
        $invoiceQuery = Invoice::where('customer_id', $customerId);
        $this->applyBranchScope($invoiceQuery, $branchId);

        // 2. Fetch Payment Vouchers (Receipts) for this customer
        $voucherQuery = PaymentVoucher::where('customer_id', $customerId)->where('type', 'receipt');
        $this->applyBranchScope($voucherQuery, $branchId);

        // Calculate Opening Balance (prior to $fromDate)
        $openingDebit = 0;
        $openingCredit = 0;

        if ($fromDate) {
            $prevInvoices = (clone $invoiceQuery)->whereDate('created_at', '<', $fromDate)->sum('total_amount');
            $prevReceipts = (clone $voucherQuery)->whereDate('payment_date', '<', $fromDate)->sum('amount');
            $openingDebit = $prevInvoices;
            $openingCredit = $prevReceipts;
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
            'unpaid_amount' => $unpaidInvoices->sum('remaining_amount'),
        ];
    }

    /**
     * Get Supplier Statement / Ledger (كشف حساب مورد)
     */
    public function getSupplierStatement(int $supplierId, ?string $fromDate = null, ?string $toDate = null, ?int $branchId = null): array
    {
        $supplier = Supplier::findOrFail($supplierId);

        // 1. Fetch Purchase Orders / Invoices for this supplier
        $poQuery = PurchaseOrder::where('supplier_id', $supplierId);
        $this->applyBranchScope($poQuery, $branchId);

        // 2. Fetch Payment Vouchers (Payment to supplier)
        $voucherQuery = PaymentVoucher::where('supplier_id', $supplierId)->where('type', 'payment');
        $this->applyBranchScope($voucherQuery, $branchId);

        $openingDebit = 0;
        $openingCredit = 0;

        if ($fromDate) {
            $prevPurchases = (clone $poQuery)->whereDate('created_at', '<', $fromDate)->sum('total_amount');
            $prevPayments = (clone $voucherQuery)->whereDate('payment_date', '<', $fromDate)->sum('amount');
            $openingCredit = $prevPurchases;
            $openingDebit = $prevPayments;
        }

        $openingBalance = $openingCredit - $openingDebit; // Positive means supplier is owed money

        if ($fromDate) {
            $poQuery->whereDate('created_at', '>=', $fromDate);
            $voucherQuery->whereDate('payment_date', '>=', $fromDate);
        }
        if ($toDate) {
            $poQuery->whereDate('created_at', '<=', $toDate);
            $voucherQuery->whereDate('payment_date', '<=', $toDate);
        }

        $purchaseOrders = $poQuery->with(['branch', 'creator'])->get();
        $vouchers = $voucherQuery->with(['creator', 'cashbox.branch'])->get();

        $ledger = collect();

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
        $query = Invoice::with(['customer', 'branch', 'creator']);

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

        return [
            'invoices' => $query->latest()->paginate(20)->withQueryString(),
            'raw_query' => $summaryQuery,
            'total_invoices' => $summaryQuery->count(),
            'total_subtotal' => (float) $summaryQuery->sum('subtotal'),
            'total_tax' => (float) $summaryQuery->sum('tax_amount'),
            'total_discount' => (float) $summaryQuery->sum('discount_amount'),
            'total_net' => (float) $summaryQuery->sum('total_amount'),
            'total_paid' => (float) (clone $summaryQuery)->where('status', 'paid')->sum('total_amount'),
            'total_remaining' => (float) (clone $summaryQuery)->where('status', '!=', 'paid')->sum('total_amount'),
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
}
