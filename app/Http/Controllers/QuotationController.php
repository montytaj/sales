<?php

namespace App\Http\Controllers;

use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\Invoice;
use App\Models\Customer;
use App\Models\CustomerOrder;
use App\Models\Service;
use App\Models\Branch;
use App\Models\ActivityLog;
use App\Services\SalesCalculationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class QuotationController extends Controller
{
    use AuthorizesRequests;

    protected SalesCalculationService $calcService;

    public function __construct(SalesCalculationService $calcService)
    {
        $this->calcService = $calcService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-quotations');

        $query = Quotation::with(['customer', 'branch', 'approver']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('quotation_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('approved')) {
            $query->where('is_approved', $request->input('approved') === '1');
        }

        $quotations = $query->latest()->paginate(15)->withQueryString();

        return view('sales.quotations.index', compact('quotations'));
    }

    public function create(Request $request)
    {
        $this->authorize('create-quotations');

        $customers = Customer::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        $selectedOrder = null;
        if ($request->filled('order_id')) {
            $selectedOrder = CustomerOrder::find($request->input('order_id'));
        }

        return view('sales.quotations.create', compact('customers', 'services', 'branches', 'selectedOrder'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-quotations');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'customer_order_id' => ['nullable', 'exists:customer_orders,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],

            // Items validation
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_of_measure' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated, $request) {
            // Server-side calculation of items
            $calculatedItems = [];
            foreach ($validated['items'] as $index => $itemData) {
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $discount = (float) ($itemData['discount_amount'] ?? 0.0);
                $taxPercent = (float) ($itemData['tax_percent'] ?? setting('tax_percentage', 15.00));

                $calc = $this->calcService->calculateItem($qty, $price, $discount, $taxPercent);

                $calculatedItems[] = array_merge($itemData, $calc, ['sort_order' => $index + 1]);
            }

            $totals = $this->calcService->calculateDocumentTotals($calculatedItems);

            $quotation = Quotation::create([
                'quotation_number' => Quotation::generateQuotationNumber(),
                'customer_id' => $validated['customer_id'],
                'customer_order_id' => $validated['customer_order_id'] ?? null,
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => 'draft',
                'is_approved' => false,
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($calculatedItems as $item) {
                $quotation->items()->create($item);
            }

            // Update customer order status if linked
            if ($quotation->customer_order_id) {
                CustomerOrder::where('id', $quotation->customer_order_id)->update(['status' => 'quoted']);
            }

            ActivityLog::log(
                'quotation_created',
                $quotation,
                "Created quotation {$quotation->quotation_number} total SAR {$quotation->total_amount}"
            );

            return redirect()->route('quotations.show', $quotation)->with('success', 'تم إنشاء عرض السعر بنجاح.');
        });
    }

    public function show($locale, Quotation $quotation)
    {
        $this->authorize('view-quotations');

        $quotation->load(['customer', 'branch', 'items.service', 'approver', 'creator', 'invoice']);

        return view('sales.quotations.show', compact('quotation'));
    }

    public function edit($locale, Quotation $quotation)
    {
        $this->authorize('edit-quotations');

        // Prevent editing if approved unless user has approve-quotations permission
        if ($quotation->is_approved && !auth()->user()->can('approve-quotations')) {
            return back()->with('error', 'لا يمكن تعديل عرض سعر تم اعتماده مسبقاً إلا بموافقة الإدارة.');
        }

        $customers = Customer::where('is_active', true)->get();
        $services = Service::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $quotation->load('items');

        return view('sales.quotations.edit', compact('quotation', 'customers', 'services', 'branches'));
    }

    public function update(Request $request, $locale, Quotation $quotation)
    {
        $this->authorize('edit-quotations');

        if ($quotation->is_approved && !auth()->user()->can('approve-quotations')) {
            return back()->with('error', 'لا يمكن تعديل عرض سعر تم اعتماده مسبقاً إلا بموافقة الإدارة.');
        }

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'issue_date' => ['required', 'date'],
            'expiry_date' => ['nullable', 'date', 'after_or_equal:issue_date'],
            'status' => ['required', 'in:draft,sent,accepted,rejected,expired,cancelled'],
            'notes' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],

            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.item_name' => ['required', 'string', 'max:255'],
            'items.*.description' => ['nullable', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.0001'],
            'items.*.unit_of_measure' => ['required', 'string', 'max:50'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'items.*.discount_amount' => ['nullable', 'numeric', 'min:0'],
            'items.*.tax_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
        ]);

        return DB::transaction(function () use ($validated, $quotation) {
            $calculatedItems = [];
            foreach ($validated['items'] as $index => $itemData) {
                $qty = (float) $itemData['quantity'];
                $price = (float) $itemData['unit_price'];
                $discount = (float) ($itemData['discount_amount'] ?? 0.0);
                $taxPercent = (float) ($itemData['tax_percent'] ?? 15.0);

                $calc = $this->calcService->calculateItem($qty, $price, $discount, $taxPercent);
                $calculatedItems[] = array_merge($itemData, $calc, ['sort_order' => $index + 1]);
            }

            $totals = $this->calcService->calculateDocumentTotals($calculatedItems);

            $quotation->update([
                'customer_id' => $validated['customer_id'],
                'branch_id' => $validated['branch_id'] ?? null,
                'status' => $validated['status'],
                'issue_date' => $validated['issue_date'],
                'expiry_date' => $validated['expiry_date'] ?? null,
                'subtotal' => $totals['subtotal'],
                'discount_amount' => $totals['discount_amount'],
                'tax_amount' => $totals['tax_amount'],
                'total_amount' => $totals['total_amount'],
                'notes' => $validated['notes'] ?? null,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
            ]);

            $quotation->items()->delete();
            foreach ($calculatedItems as $item) {
                $quotation->items()->create($item);
            }

            ActivityLog::log(
                'quotation_updated',
                $quotation,
                "Updated quotation {$quotation->quotation_number} status {$quotation->status}"
            );

            return redirect()->route('quotations.show', $quotation)->with('success', 'تم تحديث عرض السعر بنجاح.');
        });
    }

    public function approve($locale, Quotation $quotation)
    {
        $this->authorize('approve-quotations');

        $quotation->update([
            'is_approved' => true,
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'status' => $quotation->status === 'draft' ? 'sent' : $quotation->status,
        ]);

        ActivityLog::log(
            'quotation_approved',
            $quotation,
            "Approved quotation {$quotation->quotation_number}"
        );

        return back()->with('success', 'تم اعتماد عرض السعر بنجاح وقفل التعديل.');
    }

    public function convertToInvoice($locale, Quotation $quotation)
    {
        $this->authorize('convert-quotation-to-invoice');

        if ($quotation->invoice) {
            return back()->with('error', 'عرض السعر تم تحويله إلى فاتورة مسبقاً.');
        }

        return DB::transaction(function () use ($quotation) {
            $invoice = Invoice::create([
                'invoice_number' => Invoice::generateInvoiceNumber(),
                'quotation_id' => $quotation->id,
                'customer_id' => $quotation->customer_id,
                'branch_id' => $quotation->branch_id,
                'status' => 'issued',
                'issue_date' => now(),
                'due_date' => now()->addDays($quotation->customer->credit_period_days ?? 14),
                'subtotal' => $quotation->subtotal,
                'discount_amount' => $quotation->discount_amount,
                'tax_amount' => $quotation->tax_amount,
                'total_amount' => $quotation->total_amount,
                'notes' => $quotation->notes,
                'created_by' => auth()->id(),
            ]);

            foreach ($quotation->items as $qItem) {
                $invoice->items()->create([
                    'service_id' => $qItem->service_id,
                    'item_name' => $qItem->item_name,
                    'description' => $qItem->description,
                    'quantity' => $qItem->quantity,
                    'unit_of_measure' => $qItem->unit_of_measure,
                    'unit_price' => $qItem->unit_price,
                    'discount_amount' => $qItem->discount_amount,
                    'tax_percent' => $qItem->tax_percent,
                    'tax_amount' => $qItem->tax_amount,
                    'subtotal' => $qItem->subtotal,
                    'total' => $qItem->total,
                    'sort_order' => $qItem->sort_order,
                ]);
            }

            $quotation->update(['status' => 'accepted']);

            if ($quotation->customerOrder) {
                $quotation->customerOrder->update(['status' => 'converted']);
            }

            ActivityLog::log(
                'quotation_converted_to_invoice',
                $quotation,
                "Converted quotation {$quotation->quotation_number} to invoice {$invoice->invoice_number}"
            );

            return redirect()->route('invoices.show', $invoice)->with('success', "تم تحويل عرض السعر إلى فاتورة مبيعات رقم ({$invoice->invoice_number}) بنجاح.");
        });
    }

    public function print($locale, Quotation $quotation)
    {
        $this->authorize('print-sales-documents');

        $quotation->load(['customer', 'branch', 'items.service', 'approver', 'creator']);

        ActivityLog::log(
            'quotation_printed',
            $quotation,
            "Printed quotation {$quotation->quotation_number}"
        );

        return view('sales.quotations.print', compact('quotation'));
    }
}
