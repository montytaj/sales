<?php

namespace App\Http\Controllers;

use App\Models\PaymentVoucher;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Cashbox;
use App\Models\ActivityLog;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PaymentVoucherController extends Controller
{
    use AuthorizesRequests;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-payment-vouchers');

        $query = PaymentVoucher::with(['customer', 'supplier', 'invoice', 'cashbox', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('voucher_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $vouchers = $query->latest()->paginate(15)->withQueryString();

        return view('finance.payments.index', compact('vouchers'));
    }

    public function create(Request $request)
    {
        $this->authorize('create-payment-vouchers');

        $customers = Customer::where('is_active', true)->get();
        $suppliers = Supplier::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get();
        $invoices = Invoice::whereNotIn('status', ['paid', 'cancelled'])->get();

        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::find($request->input('invoice_id'));
        }

        return view('finance.payments.create', compact('customers', 'suppliers', 'cashboxes', 'invoices', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-payment-vouchers');

        $validated = $request->validate([
            'type' => ['required', 'in:receipt,payment,transfer'],
            'customer_id' => ['nullable', 'exists:customers,id'],
            'supplier_id' => ['nullable', 'exists:suppliers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'cashbox_id' => ['required', 'exists:cashboxes,id'],
            'target_cashbox_id' => ['nullable', 'exists:cashboxes,id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],

            // Payment lines validation (Split payments)
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.payment_method' => ['required', 'in:cash,bank_transfer,card,cheque,credit,e_wallet,other'],
            'lines.*.amount' => ['required', 'numeric', 'min:0.01'],
            'lines.*.reference_number' => ['nullable', 'string', 'max:255'],
            'lines.*.notes' => ['nullable', 'string'],

            // Cheque details if cheque method is selected
            'cheque_number' => ['nullable', 'string', 'required_if:lines.0.payment_method,cheque'],
            'bank_name' => ['nullable', 'string', 'required_if:lines.0.payment_method,cheque'],
            'drawer_name' => ['nullable', 'string', 'required_if:lines.0.payment_method,cheque'],
            'issue_date' => ['nullable', 'date', 'required_if:lines.0.payment_method,cheque'],
            'due_date' => ['nullable', 'date', 'required_if:lines.0.payment_method,cheque'],
        ]);

        try {
            $chequeData = [];
            if (!empty($validated['cheque_number'])) {
                $chequeData = [
                    'cheque_number' => $validated['cheque_number'],
                    'bank_name' => $validated['bank_name'],
                    'drawer_name' => $validated['drawer_name'],
                    'issue_date' => $validated['issue_date'],
                    'due_date' => $validated['due_date'],
                    'notes' => $validated['notes'] ?? null,
                ];
            }

            $voucher = $this->paymentService->createVoucher($validated, $validated['lines'], $chequeData);

            return redirect()->route('payments.show', $voucher)->with('success', 'تم إنشاء السند المالي بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->withInput()->with('error', 'حدث خطأ أثناء معالجة السند: ' . $e->getMessage());
        }
    }

    public function show($locale, PaymentVoucher $payment)
    {
        $this->authorize('view-payment-vouchers');

        $payment->load(['customer', 'supplier', 'invoice', 'cashbox', 'targetCashbox', 'lines', 'cheques', 'creator']);

        return view('finance.payments.show', ['voucher' => $payment]);
    }

    public function cancel($locale, PaymentVoucher $payment)
    {
        $this->authorize('cancel-payment-vouchers');

        try {
            $this->paymentService->cancelVoucher($payment);
            return back()->with('success', 'تم إلغاء السند المالي وتعديل الرصيد بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function print($locale, PaymentVoucher $payment)
    {
        $this->authorize('print-payment-receipts');

        $payment->load(['customer', 'supplier', 'invoice', 'cashbox', 'lines', 'cheques', 'creator']);

        ActivityLog::log(
            'payment_receipt_printed',
            $payment,
            "Printed receipt for voucher {$payment->voucher_number}"
        );

        return view('finance.payments.print', ['voucher' => $payment]);
    }
}
