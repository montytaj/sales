<?php

namespace App\Http\Controllers;

use App\Models\CustomerOrder;
use App\Models\Customer;
use App\Models\Branch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerOrderController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-customer-orders');

        $query = CustomerOrder::with(['customer', 'branch', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('requirements_summary', 'like', "%{$search}%")
                  ->orWhereHas('customer', function ($cq) use ($search) {
                      $cq->where('name', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $orders = $query->latest()->paginate(15)->withQueryString();

        return view('sales.orders.index', compact('orders'));
    }

    public function create()
    {
        $this->authorize('create-customer-orders');

        $customers = Customer::where('is_active', true)->get();
        $branches = Branch::where('is_active', true)->get();

        return view('sales.orders.create', compact('customers', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-customer-orders');

        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'requirements_summary' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $order = CustomerOrder::create([
            'order_number' => CustomerOrder::generateOrderNumber(),
            'customer_id' => $validated['customer_id'],
            'branch_id' => $validated['branch_id'] ?? null,
            'status' => 'pending',
            'requirements_summary' => $validated['requirements_summary'],
            'notes' => $validated['notes'] ?? null,
            'created_by' => auth()->id(),
        ]);

        ActivityLog::log(
            'customer_order_created',
            $order,
            "Created customer order {$order->order_number}"
        );

        return redirect()->route('customer-orders.index')->with('success', 'تم إضافة طلب العميل بنجاح.');
    }

    public function show($locale, CustomerOrder $customerOrder)
    {
        $this->authorize('view-customer-orders');

        $customerOrder->load(['customer', 'branch', 'creator', 'quotations']);

        return view('sales.orders.show', compact('customerOrder'));
    }
}
