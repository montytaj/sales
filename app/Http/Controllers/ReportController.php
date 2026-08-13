<?php

namespace App\Http\Controllers;

use App\Services\ReportService;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Branch;
use App\Models\Cashbox;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ReportController extends Controller
{
    use AuthorizesRequests;

    protected ReportService $reportService;

    public function __construct(ReportService $reportService)
    {
        $this->reportService = $reportService;
    }

    /**
     * Check permission before viewing any report
     */
    protected function checkReportPermission(string $permission, string $fallbackPermission = 'reports.access'): void
    {
        $user = Auth::user();
        if (!$user) {
            abort(401);
        }

        if ($user->hasRole('system-admin') || $user->hasRole('general-manager')) {
            return;
        }

        if ($user->can($permission) || $user->can($fallbackPermission) || $user->can('reports.financial.view') || $user->can('reports.financial_statements.view')) {
            return;
        }

        abort(403, 'عفواً، لا تملك الصلاحية المحاسبية اللازمة للوصول إلى هذا التقرير.');
    }

    /**
     * Reports Hub Index
     */
    public function index()
    {
        $this->checkReportPermission('reports.access');
        $branches = Branch::where('is_active', true)->get();
        return view('reports.index', compact('branches'));
    }

    /**
     * Customer Ledger / Statement of Account (كشف حساب عميل)
     */
    public function customerStatement(Request $request)
    {
        $this->checkReportPermission('reports.customers.view');

        $customers = Customer::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        $selectedCustomerId = $request->input('customer_id', $customers->first()?->id);
        $statementData = null;

        if ($selectedCustomerId) {
            $statementData = $this->reportService->getCustomerStatement(
                (int) $selectedCustomerId,
                $request->input('from_date'),
                $request->input('to_date'),
                $request->input('branch_id') ? (int) $request->input('branch_id') : null
            );
        }

        if ($request->input('export') === 'csv' && $statementData) {

            return $this->exportLedgerCsv(
                $statementData['ledger'],
                "customer_statement_{$selectedCustomerId}.csv",
                $statementData['customer']->name,
                $statementData['opening_balance'],
                $statementData['ending_balance']
            );
        }

        if ($request->input('export') === 'print' && $statementData) {

            return view('reports.print', [
                'title' => __('reports.customer_statement') . ' - ' . $statementData['customer']->name,
                'subtitle' => 'فترة التقرير: ' . ($request->input('from_date') ?? 'البداية') . ' إلى ' . ($request->input('to_date') ?? 'الآن'),
                'data' => $statementData['ledger'],
                'columns' => [
                    'date' => __('reports.date') ?? 'التاريخ',
                    'document_number' => __('reports.document_no') ?? 'رقم المستند',
                    'document_type' => __('reports.document_type') ?? 'نوع المستند',
                    'description' => __('reports.description') ?? 'البيان',
                    'debit' => __('reports.debit') ?? 'مدين',
                    'credit' => __('reports.credit') ?? 'دائن',
                    'running_balance' => __('reports.running_balance') ?? 'الرصيد الجاري',
                ],
                'summary' => [
                    'الرصيد الافتتاحي' => number_format($statementData['opening_balance'], 2) . ' ر.س',
                    'إجمالي المدين' => number_format($statementData['total_debit'], 2) . ' ر.س',
                    'إجمالي الدائن' => number_format($statementData['total_credit'], 2) . ' ر.س',
                    'الرصيد الختامي' => number_format($statementData['ending_balance'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.customer-statement', compact('customers', 'branches', 'statementData', 'selectedCustomerId'));
    }

    /**
     * Supplier Ledger / Statement of Account (كشف حساب مورد)
     */
    public function supplierStatement(Request $request)
    {
        $this->checkReportPermission('reports.suppliers.view');

        $suppliers = Supplier::where('is_active', true)->orderBy('name')->get();
        $branches = Branch::where('is_active', true)->get();

        $selectedSupplierId = $request->input('supplier_id', $suppliers->first()?->id);
        $statementData = null;

        if ($selectedSupplierId) {
            $statementData = $this->reportService->getSupplierStatement(
                (int) $selectedSupplierId,
                $request->input('from_date'),
                $request->input('to_date'),
                $request->input('branch_id') ? (int) $request->input('branch_id') : null
            );
        }

        if ($request->input('export') === 'csv' && $statementData) {

            return $this->exportLedgerCsv(
                $statementData['ledger'],
                "supplier_statement_{$selectedSupplierId}.csv",
                $statementData['supplier']->name,
                $statementData['opening_balance'],
                $statementData['ending_balance']
            );
        }

        if ($request->input('export') === 'print' && $statementData) {

            return view('reports.print', [
                'title' => __('reports.supplier_statement') . ' - ' . $statementData['supplier']->name,
                'subtitle' => 'فترة التقرير: ' . ($request->input('from_date') ?? 'البداية') . ' إلى ' . ($request->input('to_date') ?? 'الآن'),
                'data' => $statementData['ledger'],
                'columns' => [
                    'date' => __('reports.date') ?? 'التاريخ',
                    'document_number' => __('reports.document_no') ?? 'رقم المستند',
                    'document_type' => __('reports.document_type') ?? 'نوع المستند',
                    'description' => __('reports.description') ?? 'البيان',
                    'debit' => __('reports.debit') ?? 'مدين (مستحق للمورد)',
                    'credit' => __('reports.credit') ?? 'دائن (سداد)',
                    'running_balance' => __('reports.running_balance') ?? 'الرصيد الجاري',
                ],
                'summary' => [
                    'الرصيد الافتتاحي' => number_format($statementData['opening_balance'], 2) . ' ر.س',
                    'إجمالي التوريد' => number_format($statementData['total_credit'], 2) . ' ر.س',
                    'إجمالي السداد' => number_format($statementData['total_debit'], 2) . ' ر.س',
                    'الرصيد الختامي' => number_format($statementData['ending_balance'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.supplier-statement', compact('suppliers', 'branches', 'statementData', 'selectedSupplierId'));
    }

    /**
     * Sales Report (تقرير المبيعات)
     */
    public function sales(Request $request)
    {
        $this->checkReportPermission('reports.sales.view');

        $branches = Branch::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        $filters = $request->only(['from_date', 'to_date', 'branch_id', 'customer_id', 'status']);
        $salesData = $this->reportService->getSalesReport($filters);

        if ($request->input('export') === 'csv') {

            $headers = ['رقم الفاتورة', 'العميل', 'إجمالي الفاتورة', 'المدفوع كاش', 'حساب الكاش', 'المدفوع بنك/شبكة', 'حساب البنك', 'المتبقي (آجل)', 'طريقة الدفع', 'الحالة', 'التاريخ'];

            return $this->exportCsv(
                $salesData['raw_query']->with(['customer', 'cashAccount', 'bankAccount'])->get(),
                'sales_report.csv',
                $headers,
                function ($invoice) {
                    $cAmt = $invoice->payment_type === 'cash' ? ($invoice->cash_amount > 0 ? $invoice->cash_amount : $invoice->total_amount) : ($invoice->payment_type === 'split' ? $invoice->cash_amount : 0);
                    $bAmt = $invoice->payment_type === 'bank' ? ($invoice->bank_amount > 0 ? $invoice->bank_amount : $invoice->total_amount) : ($invoice->payment_type === 'split' ? $invoice->bank_amount : 0);
                    $dAmt = $invoice->payment_type === 'credit' ? ($invoice->due_amount > 0 ? $invoice->due_amount : $invoice->total_amount) : ($invoice->payment_type === 'split' ? $invoice->due_amount : 0);

                    return [
                        $invoice->invoice_number,
                        $invoice->customer?->name ?? '-',
                        number_format((float) $invoice->total_amount, 2, '.', ''),
                        number_format((float) $cAmt, 2, '.', ''),
                        $invoice->cashAccount?->name ?? '-',
                        number_format((float) $bAmt, 2, '.', ''),
                        $invoice->bankAccount?->name ?? '-',
                        number_format((float) $dAmt, 2, '.', ''),
                        $invoice->payment_type,
                        $invoice->status,
                        $invoice->created_at?->format('Y-m-d H:i') ?? '-',
                    ];
                }
            );
        }

        if ($request->input('export') === 'print') {

            return view('reports.print', [
                'title' => __('reports.sales_report') ?? 'تقرير المبيعات التفصيلي',
                'subtitle' => 'الفترة: ' . ($request->input('from_date') ?? 'الكل') . ' إلى ' . ($request->input('to_date') ?? 'الآن'),
                'data' => $salesData['raw_query']->get(),
                'columns' => [
                    'invoice_number' => 'رقم الفاتورة',
                    'customer.name' => 'العميل',
                    'subtotal' => 'الإجمالي قبل الضريبة',
                    'tax_amount' => 'الضريبة',
                    'net_amount' => 'الصافي',
                    'paid_amount' => 'المدفوع',
                    'status' => 'الحالة',
                    'created_at' => 'التاريخ',
                ],
                'summary' => [
                    'عدد الفواتير' => $salesData['total_invoices'],
                    'إجمالي المبيعات' => number_format($salesData['total_net'], 2) . ' ر.س',
                    'إجمالي المتبقي' => number_format($salesData['total_remaining'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.sales', array_merge($salesData, compact('branches', 'customers')));
    }

    /**
     * Workshop & CNC Report (تقرير الورشة وCNC)
     */
    public function workshop(Request $request)
    {
        $this->checkReportPermission('reports.workshop.view');

        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id', 'status', 'priority']);
        $workshopData = $this->reportService->getWorkshopReport($filters);

        if ($request->input('export') === 'csv') {

            $headers = ['رقم أمر العمل', 'العميل', 'عدد الألواح', 'القطع السليمة', 'القطع الهالكة', 'الحالة', 'الأولوية', 'التاريخ'];
            $items = isset($workshopData['raw_query'])
                ? $workshopData['raw_query']->get()
                : WorkOrder::with(['customer', 'branch'])->latest()->get();

            return $this->exportCsv(
                $items,
                'workshop_report.csv',
                $headers,
                function ($workOrder) {
                    return [
                        $workOrder->work_order_number,
                        $workOrder->customer?->name ?? '-',
                        $workOrder->sheet_count,
                        $workOrder->good_pieces,
                        $workOrder->waste_pieces,
                        $workOrder->status,
                        $workOrder->priority,
                        $workOrder->created_at?->format('Y-m-d H:i') ?? '-',
                    ];
                }
            );
        }

        return view('reports.workshop', array_merge($workshopData, compact('branches')));
    }

    /**
     * Projects & Contracts Report (تقرير المشاريع والعقود)
     */
    public function projects(Request $request)
    {
        $this->checkReportPermission('reports.projects.view');

        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id', 'status']);
        $projectsData = $this->reportService->getProjectsReport($filters);

        if ($request->input('export') === 'csv') {

            $headers = ['رقم المشروع', 'اسم المشروع', 'العميل', 'الميزانية', 'إجمالي المصاريف', 'نسبة الإنجاز', 'الحالة'];
            $items = isset($projectsData['raw_query'])
                ? $projectsData['raw_query']->get()
                : $projectsData['projects']->items();

            return $this->exportCsv(
                $items,
                'projects_report.csv',
                $headers,
                function ($project) {
                    return [
                        $project->project_number,
                        $project->name,
                        $project->customer?->name ?? '-',
                        number_format((float) $project->budget, 2, '.', ''),
                        number_format((float) ($project->total_expenses ?? 0), 2, '.', ''),
                        $project->completion_percentage . '%',
                        $project->status,
                    ];
                }
            );
        }

        return view('reports.projects', array_merge($projectsData, compact('branches')));
    }

    /**
     * Financial & Cashbox Report (حركة الخزن والمالية)
     */
    public function financial(Request $request)
    {
        $this->checkReportPermission('reports.financial.view');

        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id', 'cashbox_id', 'type']);
        $financialData = $this->reportService->getFinancialReport($filters);

        if ($request->input('export') === 'csv') {

            $headers = ['رقم السند', 'النوع', 'المبلغ', 'الخزينة', 'الفرع', 'تاريخ الدفع'];
            $items = isset($financialData['raw_query'])
                ? $financialData['raw_query']->get()
                : $financialData['vouchers']->items();

            return $this->exportCsv(
                $items,
                'financial_report.csv',
                $headers,
                function ($voucher) {
                    return [
                        $voucher->voucher_number,
                        $voucher->type === 'receipt' ? 'قبض' : 'صرف',
                        number_format((float) $voucher->amount, 2, '.', ''),
                        $voucher->cashbox?->name ?? '-',
                        $voucher->branch?->name ?? '-',
                        $voucher->payment_date ? date('Y-m-d', strtotime($voucher->payment_date)) : '-',
                    ];
                }
            );
        }

        return view('reports.financial', array_merge($financialData, compact('branches')));
    }

    /**
     * Inventory & Stock Report (تقرير المخزون)
     */
    public function inventory(Request $request)
    {
        $this->checkReportPermission('reports.inventory.view');

        $filters = $request->only(['category_id', 'category', 'search']);
        $inventoryData = $this->reportService->getInventoryReport($filters);

        if ($request->input('export') === 'csv') {

            $items = isset($inventoryData['raw_query'])
                ? $inventoryData['raw_query']->get()
                : $inventoryData['items']->items();

            $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الوحدة الأساسية', 'الكمية الحالية', 'حد الطلب الأدنى', 'تكلفة الوحدة (ر.س)', 'حالة الرصيد'];

            return $this->exportCsv(
                $items,
                'inventory_report.csv',
                $headers,
                function ($item) {
                    $qty = max(0, (float) $item->warehouseItems->sum('qty_in_base_units'));
                    $minQty = (float) ($item->min_stock_alert ?? 0);
                    return [
                        $item->item_code ?? $item->code ?? '-',
                        $item->name ?? '-',
                        $item->category?->name ?? '-',
                        $item->baseUnit?->name ?? $item->unit ?? 'قطعة',
                        number_format($qty, 2, '.', ''),
                        number_format($minQty, 2, '.', ''),
                        number_format((float) ($item->cost_price ?? 0), 2, '.', ''),
                        $qty <= $minQty ? 'منخفض المخزون' : 'رصيد آمن',
                    ];
                }
            );
        }

        return view('reports.inventory', $inventoryData);
    }

    /**
     * Warehouse Inventory Audit Report (تقرير جرد المخزن)
     */
    public function warehouseInventory(Request $request)
    {
        $this->checkReportPermission('reports.inventory.view');
        $filters = $request->only(['warehouse_id', 'from_date', 'to_date', 'category_id', 'search']);
        $reportData = $this->reportService->getWarehouseInventoryReport($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['اسم الصنف', 'التصنيف', 'الوحدة', 'الرصيد الافتتاحي', 'الوارد', 'المنصرف', 'الرصيد المتاح (الصافي)', 'سعر التكلفة (ر.س)', 'إجمالي التقييم (ر.س)'];

            return $this->exportCsv(
                $reportData['raw_items'],
                'warehouse_inventory_report.csv',
                $headers,
                function ($item) {
                    return [
                        $item['name'],
                        $item['category_name'],
                        $item['unit_name'],
                        number_format($item['opening_qty'], 2, '.', ''),
                        number_format($item['in_qty'], 2, '.', ''),
                        number_format($item['out_qty'], 2, '.', ''),
                        number_format($item['available_qty'], 2, '.', ''),
                        number_format($item['unit_cost'], 2, '.', ''),
                        number_format($item['total_valuation'], 2, '.', ''),
                    ];
                }
            );
        }

        if ($request->input('export') === 'print') {
            $whName = $reportData['selected_warehouse']?->name ?? 'جميع المخازن';
            return view('reports.print', [
                'title' => 'تقرير جرد المخزن - ' . $whName,
                'subtitle' => 'فترة التقرير: من ' . ($reportData['from_date'] ?? 'بداية النظام') . ' إلى ' . ($reportData['to_date'] ?? 'الآن'),
                'data' => $reportData['raw_items'],
                'columns' => [
                    'name' => 'اسم الصنف',
                    'category_name' => 'التصنيف',
                    'unit_name' => 'الوحدة',
                    'opening_qty' => 'الافتتاحي',
                    'in_qty' => 'الوارد',
                    'out_qty' => 'المنصرف',
                    'available_qty' => 'الرصيد المتاح',
                    'unit_cost' => 'التكلفة',
                    'total_valuation' => 'إجمالي التقييم',
                ],
                'summary' => [
                    'المخزن' => $whName,
                    'إجمالي الأصناف' => $reportData['total_items_count'],
                    'إجمالي الكمية المتوفرة' => number_format($reportData['total_stock_qty'], 2),
                    'إجمالي قيمة المخزون' => number_format($reportData['total_valuation'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.warehouse-inventory', $reportData);
    }

    /**
     * Financial Period Comparison Report (شاشة مقارنة الفترات المالية)
     */
    public function financialComparison(Request $request)
    {
        $this->checkReportPermission('reports.financial.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['p1_from', 'p1_to', 'p2_from', 'p2_to', 'branch_id']);
        $comparisonData = $this->reportService->getFinancialComparisonData($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['المؤشر المالي', 'الفترة الأولى', 'الفترة الثانية', 'الفارق', 'نسبة التغير %'];
            $rows = collect($comparisonData['metrics'])->map(function ($m) {
                return [
                    'indicator' => $m['title'],
                    'p1' => number_format($m['p1'], 2, '.', ''),
                    'p2' => number_format($m['p2'], 2, '.', ''),
                    'diff' => number_format($m['variance']['diff'], 2, '.', ''),
                    'percentage' => ($m['variance']['percentage'] >= 0 ? '+' : '') . $m['variance']['percentage'] . '%',
                ];
            });

            return $this->exportCsv(
                $rows,
                'financial_comparison_report.csv',
                $headers,
                function ($row) {
                    return [
                        $row['indicator'],
                        $row['p1'],
                        $row['p2'],
                        $row['diff'],
                        $row['percentage'],
                    ];
                }
            );
        }

        if ($request->input('export') === 'print') {
            return view('reports.print', [
                'title' => 'تقرير مقارنة الفترات المالية',
                'subtitle' => "الفترة الأولى: {$comparisonData['p1_from']} إلى {$comparisonData['p1_to']} | الفترة الثانية: {$comparisonData['p2_from']} إلى {$comparisonData['p2_to']}",
                'data' => collect($comparisonData['metrics']),
                'columns' => [
                    'title' => 'المؤشر المالي',
                    'p1' => 'الفترة الأولى',
                    'p2' => 'الفترة الثانية',
                ],
                'summary' => [
                    'الفارق في المبيعات' => number_format($comparisonData['metrics']['sales']['variance']['diff'], 2) . ' ر.س',
                    'الفارق في صافي الربح' => number_format($comparisonData['metrics']['net_profit']['variance']['diff'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.financial_comparison', array_merge($comparisonData, compact('branches')));
    }

    /**
     * Most Profitable Items Report (تقرير الأصناف الأكثر ربحية)
     */
    public function profitableItems(Request $request)
    {
        $this->checkReportPermission('reports.profitability.view');
        $branches = Branch::where('is_active', true)->get();
        $categories = \App\Models\ItemCategory::all();
        $warehouses = \App\Models\Warehouse::all();

        $filters = $request->only(['from_date', 'to_date', 'category_id', 'warehouse_id', 'branch_id', 'sort_by']);
        $reportData = $this->reportService->getProfitableItemsReport($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['كود الصنف', 'اسم الصنف', 'التصنيف', 'الكمية المباعة', 'متوسط سعر البيع', 'التكلفة الفردية', 'إجمالي الإيراد', 'إجمالي التكلفة', 'ربح الصنف', 'هامش الربح %'];

            return $this->exportCsv(
                $reportData['items'],
                'profitable_items_report.csv',
                $headers,
                function ($item) {
                    return [
                        $item['item_code'],
                        $item['item_name'],
                        $item['category'],
                        number_format($item['sold_qty'], 2, '.', ''),
                        number_format($item['avg_selling_price'], 2, '.', ''),
                        number_format($item['cost_price'], 2, '.', ''),
                        number_format($item['total_revenue'], 2, '.', ''),
                        number_format($item['total_cost'], 2, '.', ''),
                        number_format($item['profit'], 2, '.', ''),
                        number_format($item['profit_margin'], 2, '.', '') . '%',
                    ];
                }
            );
        }

        if ($request->input('export') === 'print') {
            return view('reports.print', [
                'title' => 'تقرير الأصناف الأكثر ربحية',
                'subtitle' => 'تاريخ التقرير: ' . date('Y-m-d H:i'),
                'data' => $reportData['items'],
                'columns' => [
                    'item_code' => 'الكود',
                    'item_name' => 'اسم الصنف',
                    'category' => 'التصنيف',
                    'sold_qty' => 'الكمية المباعة',
                    'total_revenue' => 'إجمالي الإيراد',
                    'total_cost' => 'إجمالي التكلفة',
                    'profit' => 'صافي الربح',
                ],
                'summary' => [
                    'إجمالي عدد الأصناف' => $reportData['total_items_count'],
                    'إجمالي الأرباح' => number_format($reportData['total_profit'], 2) . ' ر.س',
                    'متوسط هامش الربح' => $reportData['avg_profit_margin'] . '%',
                ]
            ]);
        }

        return view('reports.profitable_items', array_merge($reportData, compact('branches', 'categories', 'warehouses')));
    }

    /**
     * Balance Sheet / Statement of Financial Position (قائمة المركز المالي / الميزانية العمومية)
     */
    public function balanceSheet(Request $request)
    {
        $this->checkReportPermission('reports.balance_sheet.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['as_of_date', 'branch_id']);
        $reportData = $this->reportService->getBalanceSheetReport($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['القسم', 'البند / الحساب', 'المبلغ (ر.س)'];
            $rows = collect();

            foreach ($reportData['current_assets'] as $item) {
                $rows->push(['cat' => 'أولاً: الأصول المتداولة', 'name' => $item['name'], 'amount' => number_format($item['amount'], 2, '.', '')]);
            }
            $rows->push(['cat' => 'إجمالي الأصول المتداولة', 'name' => 'إجمالي الأصول المتداولة', 'amount' => number_format($reportData['total_current_assets'], 2, '.', '')]);

            foreach ($reportData['fixed_assets'] as $item) {
                $rows->push(['cat' => 'ثانياً: الأصول غير المتداولة', 'name' => $item['name'], 'amount' => number_format($item['amount'], 2, '.', '')]);
            }
            $rows->push(['cat' => 'صافي الأصول الثابتة', 'name' => 'صافي الأصول الثابتة', 'amount' => number_format($reportData['net_fixed_assets'], 2, '.', '')]);
            $rows->push(['cat' => 'إجمالي الأصول', 'name' => 'إجمالي الأصول النهائي', 'amount' => number_format($reportData['total_assets'], 2, '.', '')]);

            foreach ($reportData['current_liabilities'] as $item) {
                $rows->push(['cat' => 'أولاً: الخصوم المتداولة', 'name' => $item['name'], 'amount' => number_format($item['amount'], 2, '.', '')]);
            }
            $rows->push(['cat' => 'إجمالي الخصوم المتداولة', 'name' => 'إجمالي الخصوم المتداولة', 'amount' => number_format($reportData['total_current_liabilities'], 2, '.', '')]);

            foreach ($reportData['long_term_liabilities'] as $item) {
                $rows->push(['cat' => 'ثانياً: الخصوم طويلة الأجل', 'name' => $item['name'], 'amount' => number_format($item['amount'], 2, '.', '')]);
            }
            $rows->push(['cat' => 'إجمالي الخصوم', 'name' => 'إجمالي الخصوم بالكامل', 'amount' => number_format($reportData['total_liabilities'], 2, '.', '')]);

            foreach ($reportData['equity_items'] as $item) {
                $rows->push(['cat' => 'ثالثاً: حقوق الملكية', 'name' => $item['name'], 'amount' => number_format($item['amount'], 2, '.', '')]);
            }
            $rows->push(['cat' => 'إجمالي حقوق الملكية', 'name' => 'إجمالي حقوق الملكية', 'amount' => number_format($reportData['total_equity'], 2, '.', '')]);
            $rows->push(['cat' => 'إجمالي الخصوم وحقوق الملكية', 'name' => 'إجمالي الخصوم وحقوق الملكية النهائي', 'amount' => number_format($reportData['total_liabilities_and_equity'], 2, '.', '')]);

            return $this->exportCsv(
                $rows,
                "balance_sheet_{$reportData['as_of_date']}.csv",
                $headers,
                fn($row) => [$row['cat'], $row['name'], $row['amount']]
            );
        }

        if ($request->input('export') === 'print') {
            return view('reports.print', [
                'title' => 'قائمة المركز المالي (الميزانية العمومية)',
                'subtitle' => "بتاريخ: {$reportData['as_of_date']}",
                'data' => collect(array_merge($reportData['current_assets'], $reportData['fixed_assets'], $reportData['current_liabilities'], $reportData['equity_items'])),
                'columns' => ['name' => 'البيان', 'amount' => 'المبلغ'],
                'summary' => [
                    'إجمالي الأصول' => number_format($reportData['total_assets'], 2) . ' ر.س',
                    'إجمالي الخصوم' => number_format($reportData['total_liabilities'], 2) . ' ر.س',
                    'إجمالي حقوق الملكية' => number_format($reportData['total_equity'], 2) . ' ر.س',
                    'إجمالي الخصوم وحقوق الملكية' => number_format($reportData['total_liabilities_and_equity'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.balance-sheet', array_merge($reportData, compact('branches')));
    }

    /**
     * Income Statement (قائمة الدخل / الأرباح والخسائر)
     */
    public function incomeStatement(Request $request)
    {
        $this->checkReportPermission('reports.income_statement.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id']);
        $reportData = $this->reportService->getIncomeStatementReport($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['البيان', 'المبلغ (ر.س)'];
            $rows = [
                ['name' => 'المبيعات الإجمالية', 'amount' => number_format($reportData['gross_sales'], 2, '.', '')],
                ['name' => '(-) مردودات ومسموحات المبيعات', 'amount' => '(' . number_format($reportData['sales_returns'], 2, '.', '') . ')'],
                ['name' => 'صافي المبيعات', 'amount' => number_format($reportData['net_sales'], 2, '.', '')],
                ['name' => '(-) تكلفة البضاعة المباعة (COGS)', 'amount' => '(' . number_format($reportData['cogs'], 2, '.', '') . ')'],
                ['name' => 'مجمل الربح', 'amount' => number_format($reportData['gross_profit'], 2, '.', '')],
            ];

            foreach ($reportData['operating_expenses'] as $exp) {
                $rows[] = ['name' => $exp['name'], 'amount' => '(' . number_format($exp['amount'], 2, '.', '') . ')'];
            }
            $rows[] = ['name' => 'إجمالي المصروفات التشغيلية', 'amount' => '(' . number_format($reportData['total_operating_expenses'], 2, '.', '') . ')'];
            $rows[] = ['name' => 'الربح التشغيلي', 'amount' => number_format($reportData['operating_profit'], 2, '.', '')];
            $rows[] = ['name' => '(+) إيرادات أخرى', 'amount' => number_format($reportData['other_income'], 2, '.', '')];
            $rows[] = ['name' => '(-) مصروف فوائد', 'amount' => '(' . number_format($reportData['interest_expense'], 2, '.', '') . ')'];
            $rows[] = ['name' => 'صافي الربح قبل الضريبة', 'amount' => number_format($reportData['profit_before_tax'], 2, '.', '')];
            $rows[] = ['name' => '(-) ضريبة الدخل / القيمة المضافة', 'amount' => '(' . number_format($reportData['tax_amount'], 2, '.', '') . ')'];
            $rows[] = ['name' => 'صافي الربح بعد الضريبة', 'amount' => number_format($reportData['net_profit_after_tax'], 2, '.', '')];

            return $this->exportCsv(
                collect($rows),
                "income_statement_{$reportData['from_date']}_to_{$reportData['to_date']}.csv",
                $headers,
                fn($r) => [$r['name'], $r['amount']]
            );
        }

        if ($request->input('export') === 'print') {
            return view('reports.print', [
                'title' => 'قائمة الدخل (الأرباح والخسائر)',
                'subtitle' => "الفترة: من {$reportData['from_date']} إلى {$reportData['to_date']}",
                'data' => collect([
                    ['name' => 'صافي المبيعات', 'amount' => number_format($reportData['net_sales'], 2)],
                    ['name' => 'تكلفة البضاعة المباعة', 'amount' => number_format($reportData['cogs'], 2)],
                    ['name' => 'مجمل الربح', 'amount' => number_format($reportData['gross_profit'], 2)],
                    ['name' => 'إجمالي المصروفات التشغيلية', 'amount' => number_format($reportData['total_operating_expenses'], 2)],
                    ['name' => 'الربح التشغيلي', 'amount' => number_format($reportData['operating_profit'], 2)],
                    ['name' => 'صافي الربح بعد الضريبة', 'amount' => number_format($reportData['net_profit_after_tax'], 2)],
                ]),
                'columns' => ['name' => 'البيان', 'amount' => 'المبلغ (ر.س)'],
                'summary' => [
                    'هامش صافي الربح' => $reportData['profit_margin'] . '%',
                    'صافي الربح النهائي' => number_format($reportData['net_profit_after_tax'], 2) . ' ر.س',
                ]
            ]);
        }

        return view('reports.income-statement', array_merge($reportData, compact('branches')));
    }

    /**
     * Trial Balance (ميزان المراجعة)
     */
    public function trialBalance(Request $request)
    {
        $this->checkReportPermission('reports.trial_balance.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'level']);
        $reportData = $this->reportService->getTrialBalanceReport($filters);

        if ($request->input('export') === 'csv') {
            $headers = ['رمز الحساب', 'اسم الحساب', 'المستوى', 'مدين افتتاحي', 'دائن افتتاحي', 'حركة مدين', 'حركة دائن', 'رصيد مدين', 'رصيد دائن'];
            return $this->exportCsv(
                collect($reportData['rows']),
                "trial_balance_{$reportData['from_date']}_to_{$reportData['to_date']}.csv",
                $headers,
                fn($r) => [
                    $r['code'], $r['name'], $r['level'],
                    number_format($r['opening_debit'], 2, '.', ''),
                    number_format($r['opening_credit'], 2, '.', ''),
                    number_format($r['period_debit'], 2, '.', ''),
                    number_format($r['period_credit'], 2, '.', ''),
                    number_format($r['ending_debit'], 2, '.', ''),
                    number_format($r['ending_credit'], 2, '.', '')
                ]
            );
        }

        return view('reports.trial-balance', array_merge($reportData, compact('branches')));
    }

    /**
     * Statement of Cash Flows (قائمة التدفقات النقدية)
     */
    public function cashFlow(Request $request)
    {
        $this->checkReportPermission('reports.cash_flow.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id']);
        $reportData = $this->reportService->getCashFlowReport($filters);

        return view('reports.cash-flow', array_merge($reportData, compact('branches')));
    }

    /**
     * Statement of Changes in Equity (قائمة التغيرات في حقوق الملكية)
     */
    public function equityChanges(Request $request)
    {
        $this->checkReportPermission('reports.equity_changes.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['from_date', 'to_date', 'branch_id']);
        $reportData = $this->reportService->getEquityChangesReport($filters);

        return view('reports.equity-changes', array_merge($reportData, compact('branches')));
    }

    /**
     * General Ledger Report (دفتر الأستاذ العام)
     */
    public function generalLedger(Request $request)
    {
        $this->checkReportPermission('reports.general_ledger.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['account_id', 'from_date', 'to_date']);
        $reportData = $this->reportService->getGeneralLedgerReport($filters);

        return view('reports.general-ledger', array_merge($reportData, compact('branches')));
    }

    /**
     * Account Balances Tree Report (كشف أرصدة الحسابات)
     */
    public function accountBalances(Request $request)
    {
        $this->checkReportPermission('reports.account_balances.view', 'reports.financial_statements.view');
        $branches = Branch::where('is_active', true)->get();
        $filters = $request->only(['as_of_date']);
        $reportData = $this->reportService->getAccountBalancesReport($filters);

        return view('reports.account-balances', array_merge($reportData, compact('branches')));
    }

    /**
     * Ledger CSV Export Utility with BOM and Running Balance.
     */

    protected function exportLedgerCsv(\Illuminate\Support\Collection $ledger, string $filename, string $entityName, float $openingBalance, float $endingBalance)

    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($ledger, $entityName, $openingBalance, $endingBalance) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF"); // UTF-8 BOM

            fputcsv($file, ['كشف حساب تفصيلي - Workshop ERP']);
            fputcsv($file, ['الجهة / العميل:', $entityName]);
            fputcsv($file, ['تاريخ التصدير:', date('Y-m-d H:i:s')]);
            fputcsv($file, ['الرصيد الافتتاحي:', number_format($openingBalance, 2)]);
            fputcsv($file, []);

            fputcsv($file, ['التاريخ', 'رقم المستند', 'نوع المستند', 'البيان', 'مدين', 'دائن', 'الرصيد الجاري', 'الفرع', 'المستخدم']);

            foreach ($ledger as $row) {
                fputcsv($file, [
                    $row['date'],
                    $row['document_number'],
                    $row['document_type'],
                    $row['description'],
                    number_format($row['debit'], 2),
                    number_format($row['credit'], 2),
                    number_format($row['running_balance'], 2),
                    $row['branch'] ?? '-',
                    $row['user'] ?? '-',
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['الرصيد الختامي النهائي:', number_format($endingBalance, 2)]);

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }

    /**
     * Utility CSV Exporter.
     */
    protected function exportCsv($collection, string $filename, array $columns, ?callable $rowMapper = null)
    {
        $headers = [
            "Content-type" => "text/csv; charset=UTF-8",
            "Content-Disposition" => "attachment; filename={$filename}",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $callback = function () use ($collection, $columns, $rowMapper) {
            $file = fopen('php://output', 'w');
            fputs($file, "\xEF\xBB\xBF");
            fputcsv($file, $columns);

            foreach ($collection as $row) {
                if ($rowMapper) {
                    $line = $rowMapper($row);
                } else {
                    $line = [];
                    foreach ($columns as $col) {
                        $parts = explode('.', $col);
                        $val = $row;
                        foreach ($parts as $part) {
                            $val = is_array($val) ? ($val[$part] ?? null) : ($val?->{$part} ?? '');
                        }
                        if (is_object($val) && !method_exists($val, '__toString')) {
                            $val = '';
                        }
                        $line[] = (string) $val;
                    }
                }
                fputcsv($file, $line);
            }

            fclose($file);
        };

        return Response::stream($callback, 200, $headers);
    }
}
