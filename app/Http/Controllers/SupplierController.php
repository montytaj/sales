<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use App\Models\Branch;
use App\Models\ActivityLog;
use App\Http\Requests\StoreSupplierRequest;
use App\Http\Requests\UpdateSupplierRequest;
use App\Http\Requests\UploadSupplierAttachmentRequest;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;


class SupplierController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-suppliers');

        $query = Supplier::with('branch');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('contact_person', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('services_provided', 'like', "%{$search}%");
            });
        }

        if ($request->filled('rating')) {
            $query->where('rating', $request->input('rating'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $suppliers = $query->latest()->paginate(15)->withQueryString();

        return view('suppliers.index', compact('suppliers'));
    }

    public function create()
    {
        $this->authorize('create-suppliers');

        $branches = Branch::where('is_active', true)->get();

        return view('suppliers.create', compact('branches'));
    }

    public function store(StoreSupplierRequest $request)
    {
        $this->authorize('create-suppliers');

        $validated = $request->validated();

        $supplier = Supplier::create([
            'code' => Supplier::generateCode(),
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'services_provided' => $validated['services_provided'] ?? null,
            'rating' => $validated['rating'],
            'is_active' => $request->boolean('is_active', true),
            'notes' => $validated['notes'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $path = $file->store('attachments/suppliers', 'public');

            $supplier->attachments()->create([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_size' => $file->getSize(),
                'mime_type' => $file->getClientMimeType(),
                'uploaded_by' => auth()->id(),
            ]);
        }

        ActivityLog::log(
            'supplier_created',
            $supplier,
            "Created supplier {$supplier->name} ({$supplier->code})"
        );

        return redirect()->route('suppliers.index')->with('success', __('suppliers.created_successfully'));
    }

    public function show($locale, Supplier $supplier)
    {
        $this->authorize('view-suppliers');

        $supplier->load(['branch', 'attachments.uploader']);

        return view('suppliers.show', compact('supplier'));
    }

    public function edit($locale, Supplier $supplier)
    {
        $this->authorize('edit-suppliers');

        $branches = Branch::where('is_active', true)->get();

        return view('suppliers.edit', compact('supplier', 'branches'));
    }

    public function update(UpdateSupplierRequest $request, $locale, Supplier $supplier)
    {
        $this->authorize('edit-suppliers');

        $validated = $request->validated();

        $supplier->update([
            'name' => $validated['name'],
            'company_name' => $validated['company_name'] ?? null,
            'contact_person' => $validated['contact_person'] ?? null,
            'phone' => $validated['phone'],
            'phone_secondary' => $validated['phone_secondary'] ?? null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'] ?? null,
            'cr_number' => $validated['cr_number'] ?? null,
            'vat_number' => $validated['vat_number'] ?? null,
            'services_provided' => $validated['services_provided'] ?? null,
            'rating' => $validated['rating'],
            'is_active' => $request->boolean('is_active'),
            'notes' => $validated['notes'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
        ]);

        ActivityLog::log(
            'supplier_updated',
            $supplier,
            "Updated supplier {$supplier->name} ({$supplier->code})"
        );

        return redirect()->route('suppliers.index')->with('success', __('suppliers.updated_successfully'));
    }

    public function destroy($locale, Supplier $supplier)
    {
        $this->authorize('delete-suppliers');

        ActivityLog::log(
            'supplier_deleted',
            $supplier,
            "Deleted supplier {$supplier->name} ({$supplier->code})"
        );

        $supplier->delete();

        return redirect()->route('suppliers.index')->with('success', __('suppliers.deleted_successfully'));
    }

    public function uploadAttachment(UploadSupplierAttachmentRequest $request, $locale, Supplier $supplier)
    {
        $this->authorize('edit-suppliers');

        $file = $request->file('attachment');
        $path = $file->store('attachments/suppliers', 'public');

        $supplier->attachments()->create([
            'file_name' => $file->getClientOriginalName(),
            'file_path' => $path,
            'file_size' => $file->getSize(),
            'mime_type' => $file->getClientMimeType(),
            'uploaded_by' => auth()->id(),
        ]);

        return back()->with('success', __('suppliers.updated_successfully'));
    }
}
