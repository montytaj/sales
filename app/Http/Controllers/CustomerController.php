<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Branch;
use App\Models\Attachment;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CustomerController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-customers');

        $query = Customer::with('branch');

        // Scope to accessible branches if user is restricted
        $user = auth()->user();
        if (!$user->hasRole('system-admin') && !$user->hasRole('general-manager')) {
            $accessibleBranchIds = $user->accessibleBranches()->pluck('id')->toArray();
            if (!empty($accessibleBranchIds)) {
                $query->where(function ($q) use ($accessibleBranchIds) {
                    $q->whereIn('branch_id', $accessibleBranchIds)
                      ->orWhereNull('branch_id');
                });
            }
        }

        // Search by name, code, phone, CR, or VAT number
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('vat_number', 'like', "%{$search}%");
            });
        }

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Filter by Category
        if ($request->filled('category')) {
            $query->where('category', $request->input('category'));
        }

        // Filter by Status
        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $customers = $query->latest()->paginate(15)->withQueryString();

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        $this->authorize('create-customers');

        $branches = Branch::where('is_active', true)->get();

        return view('customers.create', compact('branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-customers');

        $validated = $request->validate([
            'type' => ['required', 'in:individual,company'],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_secondary' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'cr_number' => ['nullable', 'string', 'max:50'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'credit_period_days' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'in:regular,vip,corporate,wholesale'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'attachment' => ['nullable', 'file', 'max:10240'], // Max 10MB
        ]);

        // Duplicate check warning validation for Phone / VAT
        if (!empty($validated['phone']) && Customer::where('phone', $validated['phone'])->exists()) {
            return back()->withInput()->with('error', __('customers.phone_already_exists'));
        }

        if (!empty($validated['vat_number']) && Customer::where('vat_number', $validated['vat_number'])->exists()) {
            return back()->withInput()->with('error', __('customers.vat_already_exists'));
        }

        $customer = Customer::create([
            'code' => Customer::generateCode(),
            'type' => $validated['type'],
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'credit_limit' => $validated['credit_limit'],
            'credit_period_days' => $validated['credit_period_days'],
            'category' => $validated['category'],
            'is_active' => $request->boolean('is_active', true),
            'notes' => $validated['notes'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        // Handle attachment file upload if present
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/customers', 'public');

            $customer->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        ActivityLog::log(
            'customer_created',
            $customer,
            "Created customer {$customer->name} ({$customer->code})"
        );

        return redirect()->route('customers.index')->with('success', __('customers.created_successfully'));
    }

    public function show($locale, Customer $customer)
    {
        $this->authorize('view-customers');

        $customer->load(['branch', 'attachments.uploader']);

        return view('customers.show', compact('customer'));
    }

    public function edit($locale, Customer $customer)
    {
        $this->authorize('edit-customers');

        $branches = Branch::where('is_active', true)->get();

        return view('customers.edit', compact('customer', 'branches'));
    }

    public function update(Request $request, $locale, Customer $customer)
    {
        $this->authorize('edit-customers');

        $validated = $request->validate([
            'type' => ['required', 'in:individual,company'],
            'name' => ['required', 'string', 'max:255'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'phone_secondary' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'cr_number' => ['nullable', 'string', 'max:50'],
            'vat_number' => ['nullable', 'string', 'max:50'],
            'credit_limit' => ['required', 'numeric', 'min:0'],
            'credit_period_days' => ['required', 'integer', 'min:0'],
            'category' => ['required', 'in:regular,vip,corporate,wholesale'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $customer->update([
            'type' => $validated['type'],
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'phone' => $validated['phone'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'credit_limit' => $validated['credit_limit'],
            'credit_period_days' => $validated['credit_period_days'],
            'category' => $validated['category'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $validated['notes'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        ActivityLog::log(
            'customer_updated',
            $customer,
            "Updated customer {$customer->name} ({$customer->code})"
        );

        return redirect()->route('customers.index')->with('success', __('customers.updated_successfully'));
    }

    public function destroy($locale, Customer $customer)
    {
        $this->authorize('delete-customers');

        ActivityLog::log(
            'customer_deleted',
            $customer,
            "Deleted customer {$customer->name} ({$customer->code})"
        );

        $customer->delete();

        return redirect()->route('customers.index')->with('success', __('customers.deleted_successfully'));
    }

    public function uploadAttachment(Request $request, $locale, Customer $customer)
    {
        $this->authorize('edit-customers');

        $request->validate([
            'attachment' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('attachment');
        $path = $file->store('attachments/customers', 'public');

        $customer->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', __('customers.updated_successfully'));
    }
}
