<?php

namespace App\Http\Controllers;

use App\Models\SignageOrder;
use App\Models\Customer;
use App\Models\Project;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SignageOrderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-signage');

        $query = SignageOrder::with(['customer', 'project']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('dimensions', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $signageOrders = $query->latest()->paginate(15)->withQueryString();

        return view('projects.signage.index', compact('signageOrders'));
    }

    public function create()
    {
        $this->authorize('create-signage');

        $customers = Customer::where('is_active', true)->get();
        $projects = Project::whereNotIn('status', ['finally_completed', 'cancelled'])->get();

        return view('projects.signage.create', compact('customers', 'projects'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-signage');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'project_id' => ['nullable', 'exists:projects,id'],
            'dimensions' => ['required', 'string', 'max:100'],
            'warranty_months' => ['nullable', 'integer', 'min:0'],
            'maintenance_notes' => ['nullable', 'string'],
            'design_file' => ['nullable', 'file', 'mimes:pdf,jpg,png,zip,dxf,dwg', 'max:10240'],
        ]);

        $designPath = null;
        if ($request->hasFile('design_file')) {
            $designPath = $request->file('design_file')->store('attachments/signage', 'public');
        }

        $signageOrder = SignageOrder::create([
            'order_number' => SignageOrder::generateOrderNumber(),
            'customer_id' => $validated['customer_id'],
            'project_id' => $validated['project_id'] ?? null,
            'dimensions' => $validated['dimensions'],
            'design_file_path' => $designPath,
            'design_approved' => false,
            'manufacturing_status' => 'pending',
            'installation_status' => 'pending',
            'warranty_months' => $validated['warranty_months'] ?? 12,
            'maintenance_notes' => $validated['maintenance_notes'] ?? null,
            'status' => 'new',
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log(
            'signage_order_created',
            $signageOrder,
            "Created signage order {$signageOrder->order_number}"
        );

        return redirect()->route('signage-orders.show', $signageOrder)->with('success', 'تم تسجيل طلب اللافتة الإعلانية بنجاح.');
    }

    public function show($locale, SignageOrder $signageOrder)
    {
        $this->authorize('view-signage');

        $signageOrder->load(['customer', 'project', 'designApprover', 'attachments']);

        return view('projects.signage.show', compact('signageOrder'));
    }

    public function approveDesign($locale, SignageOrder $signageOrder)
    {
        $this->authorize('manage-signage');

        $signageOrder->update([
            'design_approved' => true,
            'design_approved_at' => now(),
            'design_approved_by' => auth()->id(),
            'status' => 'manufacturing',
            'manufacturing_status' => 'in_progress',
        ]);

        ActivityLog::log(
            'signage_design_approved',
            $signageOrder,
            "Approved design for signage order {$signageOrder->order_number}"
        );

        return back()->with('success', 'تم اعتماد تصميم اللافتة ونقل الطلب لمرحلة التصنيع.');
    }

    public function updateStatus(Request $request, $locale, SignageOrder $signageOrder)
    {
        $this->authorize('manage-signage');

        $validated = $request->validate([
            'manufacturing_status' => ['required', 'in:pending,in_progress,completed'],
            'installation_status' => ['required', 'in:pending,scheduled,installed'],
            'installer_name' => ['nullable', 'string', 'max:255'],
            'installation_date' => ['nullable', 'date'],
        ]);

        $status = 'manufacturing';
        if ($validated['installation_status'] === 'installed') {
            $status = 'completed';
        } elseif ($validated['manufacturing_status'] === 'completed') {
            $status = 'installation';
        }

        $signageOrder->update([
            'manufacturing_status' => $validated['manufacturing_status'],
            'installation_status' => $validated['installation_status'],
            'installer_name' => $validated['installer_name'] ?? $signageOrder->installer_name,
            'installation_date' => $validated['installation_date'] ?? $signageOrder->installation_date,
            'status' => $status,
        ]);

        return back()->with('success', 'تم تحديث حالة التصنيع والتركيب للافتة بنجاح.');
    }
}
