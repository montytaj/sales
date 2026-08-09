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
     * Reports Hub Index
     */
    public function index()
    {

        $branches = Branch::where('is_active', true)->get();
        return view('reports.index', compact('branches'));
    }

    /**
     * Customer Ledger / Statement of Account (كشف حساب عميل)
     */
    public function customerStatement(Request $request)
    {

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

        $branches = Branch::where('is_active', true)->get();
        $customers = Customer::where('is_active', true)->orderBy('name')->get();

        $filters = $request->only(['from_date', 'to_date', 'branch_id', 'customer_id', 'status']);
        $salesData = $this->reportService->getSalesReport($filters);

        if ($request->input('export') === 'csv') {

            $headers = ['رقم الفاتورة', 'العميل', 'الإجمالي قبل الضريبة', 'الضريبة', 'الصافي', 'المدفوع', 'المتبقي', 'الحالة', 'التاريخ'];

            return $this->exportCsv(
                $salesData['raw_query']->get(),
                'sales_report.csv',
                $headers,
                function ($invoice) {
                    return [
                        $invoice->invoice_number,
                        $invoice->customer?->name ?? '-',
                        number_format((float) $invoice->subtotal, 2, '.', ''),
                        number_format((float) $invoice->tax_amount, 2, '.', ''),
                        number_format((float) $invoice->total_amount, 2, '.', ''),
                        number_format((float) $invoice->paid_amount, 2, '.', ''),
                        number_format((float) ($invoice->total_amount - $invoice->paid_amount), 2, '.', ''),
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
