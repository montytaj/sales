<?php

namespace App\Http\Controllers;

use App\Models\Service;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ServiceController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-services');

        $query = Service::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('service_type')) {
            $query->where('service_type', $request->input('service_type'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === 'active');
        }

        $services = $query->latest()->paginate(15)->withQueryString();

        return view('services.index', compact('services'));
    }

    public function create()
    {
        $this->authorize('create-services');

        return view('services.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create-services');

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', 'in:cnc_cutting,furniture_manufacturing,outfitting,contracting,signage,transport,installation,design'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'in:m2,m,piece,hour,project,job,kg,set'],
            'is_taxable' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $service = Service::create([
            'code' => Service::generateCode(),
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'] ?? null,
            'service_type' => $validated['service_type'],
            'default_price' => $validated['default_price'],
            'unit_of_measure' => $validated['unit_of_measure'],
            'is_taxable' => $request->boolean('is_taxable', true),
            'is_active' => $request->boolean('is_active', true),
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::log(
            'service_created',
            $service,
            "Created service {$service->name_ar} ({$service->code})"
        );

        return redirect()->route('services.index')->with('success', __('services.created_successfully'));
    }

    public function show($locale, Service $service)
    {
        $this->authorize('view-services');

        return view('services.show', compact('service'));
    }

    public function edit($locale, Service $service)
    {
        $this->authorize('edit-services');

        return view('services.edit', compact('service'));
    }

    public function update(Request $request, $locale, Service $service)
    {
        $this->authorize('edit-services');

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'service_type' => ['required', 'in:cnc_cutting,furniture_manufacturing,outfitting,contracting,signage,transport,installation,design'],
            'default_price' => ['required', 'numeric', 'min:0'],
            'unit_of_measure' => ['required', 'in:m2,m,piece,hour,project,job,kg,set'],
            'is_taxable' => ['boolean'],
            'is_active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $service->update([
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'] ?? null,
            'service_type' => $validated['service_type'],
            'default_price' => $validated['default_price'],
            'unit_of_measure' => $validated['unit_of_measure'],
            'is_taxable' => $request->boolean('is_taxable'),
            'is_active' => $request->boolean('is_active'),
            'description' => $validated['description'] ?? null,
        ]);

        ActivityLog::log(
            'service_updated',
            $service,
            "Updated service {$service->name_ar} ({$service->code})"
        );

        return redirect()->route('services.index')->with('success', __('services.updated_successfully'));
    }

    public function destroy($locale, Service $service)
    {
        $this->authorize('delete-services');

        ActivityLog::log(
            'service_deleted',
            $service,
            "Deleted service {$service->name_ar} ({$service->code})"
        );

        $service->delete();

        return redirect()->route('services.index')->with('success', __('services.deleted_successfully'));
    }
}
