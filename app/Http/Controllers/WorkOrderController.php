<?php

namespace App\Http\Controllers;

use App\Models\WorkOrder;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Branch;
use App\Models\User;
use App\Models\Attachment;
use App\Models\ActivityLog;
use App\Services\WorkOrderService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class WorkOrderController extends Controller
{
    use AuthorizesRequests;

    protected WorkOrderService $workOrderService;

    public function __construct(WorkOrderService $workOrderService)
    {
        $this->workOrderService = $workOrderService;
    }

    public function index(Request $request)
    {
        $this->authorize('view-work-orders');

        $query = WorkOrder::with(['customer', 'branch', 'assignee', 'authorization']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('work_order_number', 'like', "%{$search}%")
                  ->orWhere('sheet_type', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->input('priority'));
        }

        $workOrders = $query->latest()->paginate(15)->withQueryString();

        return view('workshop.orders.index', compact('workOrders'));
    }

    public function create(Request $request)
    {
        $this->authorize('create-work-orders');

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();
        $invoices = Invoice::whereNotIn('status', ['cancelled'])->get();
        $workers = User::where('is_active', true)->get();

        $selectedInvoice = null;
        if ($request->filled('invoice_id')) {
            $selectedInvoice = Invoice::find($request->input('invoice_id'));
        }

        return view('workshop.orders.create', compact('customers', 'branches', 'invoices', 'workers', 'selectedInvoice'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-work-orders');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'invoice_id' => ['nullable', 'exists:invoices,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'sheet_count' => ['required', 'integer', 'min:1'],
            'sheet_type' => ['required', 'string', 'max:100'],
            'dimensions' => ['nullable', 'string', 'max:100'],
            'thickness' => ['nullable', 'string', 'max:50'],
            'priority' => ['required', 'in:low,normal,high,urgent'],
            'due_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
            'cad_files.*' => ['nullable', 'file', 'mimes:pdf,jpg,png,zip,dxf,dwg', 'max:10240'],
        ]);

        $workOrder = WorkOrder::create([
            'work_order_number' => WorkOrder::generateWorkOrderNumber(),
            'customer_id' => $validated['customer_id'],
            'invoice_id' => $validated['invoice_id'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'assigned_to' => $validated['assigned_to'] ?? null,
            'assigned_by' => auth()->id(),
            'sheet_count' => $validated['sheet_count'],
            'sheet_type' => $validated['sheet_type'],
            'dimensions' => $validated['dimensions'] ?? null,
            'thickness' => $validated['thickness'] ?? null,
            'priority' => $validated['priority'],
            'due_date' => $validated['due_date'] ?? null,
            'status' => 'new',
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        // Process CAD/CAM file uploads if present
        if ($request->hasFile('cad_files')) {
            foreach ($request->file('cad_files') as $file) {
                $path = $file->store('attachments/work_orders', 'public');
                $workOrder->attachments()->create([
                    'filename' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        ActivityLog::log(
            'work_order_created',
            $workOrder,
            "Created CNC work order {$workOrder->work_order_number}"
        );

        return redirect()->route('work-orders.show', $workOrder)->with('success', 'تم إنشاء أمر العمل بنجاح.');
    }

    public function show($locale, WorkOrder $workOrder)
    {
        $this->authorize('view-work-orders');

        $workOrder->load(['customer', 'invoice', 'branch', 'assignee', 'assigner', 'authorization.authorizer', 'timeLogs.user', 'attachments', 'creator']);

        return view('workshop.orders.show', compact('workOrder'));
    }

    public function authorizeStart(Request $request, $locale = 'ar', $workOrder = null)
    {
        $workOrder = ($workOrder instanceof WorkOrder) ? $workOrder : WorkOrder::findOrFail($workOrder);
        $this->authorize('authorize-work-order-start');

        try {
            $this->workOrderService->authorizeStart($workOrder, false, null, $request->input('notes'));
            return back()->with('success', 'تم إصدار تصريح بدء العمل بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function overrideStart(Request $request, $locale = 'ar', $workOrder = null)
    {
        $workOrder = ($workOrder instanceof WorkOrder) ? $workOrder : WorkOrder::findOrFail($workOrder);
        $this->authorize('override-work-order-start');

        $validated = $request->validate([
            'override_reason' => ['required', 'string', 'min:5'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $this->workOrderService->authorizeStart($workOrder, true, $validated['override_reason'], $validated['notes'] ?? null);
            return back()->with('success', 'تم التجاوز الاستثنائي وإصدار التصريح بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function deliver(Request $request, $locale = 'ar', $workOrder = null)
    {
        $workOrder = ($workOrder instanceof WorkOrder) ? $workOrder : WorkOrder::findOrFail($workOrder);
        $this->authorize('deliver-work-orders');

        $validated = $request->validate([
            'delivery_receiver_name' => ['required', 'string', 'max:255'],
            'delivery_notes' => ['nullable', 'string'],
        ]);

        try {
            $this->workOrderService->deliverWorkOrder($workOrder, $validated['delivery_receiver_name'], $validated['delivery_notes'] ?? null);
            return back()->with('success', 'تم تسليم أمر العمل بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء التسليم: ' . $e->getMessage());
        }
    }
}
