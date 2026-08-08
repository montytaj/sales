<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class BranchController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-branches');

        $query = Branch::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $branches = $query->orderByDesc('is_main')->latest()->paginate(10)->withQueryString();

        return view('branches.index', compact('branches'));
    }

    public function create()
    {
        $this->authorize('create-branches');

        return view('branches.create');
    }

    public function store(Request $request)
    {
        $this->authorize('create-branches');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:branches,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $isMain = $request->boolean('is_main');

        // If new branch is set as main, unset other main branches
        if ($isMain) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch = Branch::create([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_main' => $isMain,
            'is_active' => $request->boolean('is_active', true),
        ]);

        ActivityLog::log(
            'branch_created',
            $branch,
            "Created branch {$branch->name} ({$branch->code})"
        );

        return redirect()->route('branches.index')->with('success', __('branches.created_successfully'));
    }

    public function show($locale, Branch $branch)
    {
        $this->authorize('view-branches');

        // Check if user has permission/access to view this branch
        if (!auth()->user()->hasAccessToBranch($branch->id)) {
            abort(403, __('branches.unauthorized_branch_access'));
        }

        $branch->load(['users', 'mainUsers']);

        return view('branches.show', compact('branch'));
    }

    public function edit($locale, Branch $branch)
    {
        $this->authorize('edit-branches');

        return view('branches.edit', compact('branch'));
    }

    public function update(Request $request, $locale, Branch $branch)
    {
        $this->authorize('edit-branches');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('branches')->ignore($branch->id)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'is_main' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $isMain = $request->boolean('is_main');

        if ($isMain && !$branch->is_main) {
            Branch::where('is_main', true)->update(['is_main' => false]);
        }

        $branch->update([
            'code' => strtoupper($validated['code']),
            'name' => $validated['name'],
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'is_main' => $isMain,
            'is_active' => $branch->is_main ? true : $request->boolean('is_active'),
        ]);

        ActivityLog::log(
            'branch_updated',
            $branch,
            "Updated branch {$branch->name} ({$branch->code})"
        );

        return redirect()->route('branches.index')->with('success', __('branches.updated_successfully'));
    }

    public function toggleStatus(Request $request, $locale, Branch $branch)
    {
        $this->authorize('toggle-branch-status');

        if ($branch->is_main) {
            return back()->with('error', __('branches.cannot_deactivate_main_branch'));
        }

        $branch->is_active = !$branch->is_active;
        $branch->save();

        ActivityLog::log(
            'branch_status_toggled',
            $branch,
            "Toggled status for branch {$branch->name} to " . ($branch->is_active ? 'Active' : 'Inactive')
        );

        return back()->with('success', __('branches.status_updated_successfully'));
    }
}
