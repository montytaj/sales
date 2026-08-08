<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Branch;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-users');

        $query = User::with(['roles', 'mainBranch']);

        // Search by name or email
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Filter by Role
        if ($request->filled('role')) {
            $roleName = $request->input('role');
            $query->whereHas('roles', function ($q) use ($roleName) {
                $q->where('name', $roleName);
            });
        }

        // Filter by Status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if ($status === 'active') {
                $query->where('is_active', true);
            } elseif ($status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $this->authorize('create-users');

        $roles = Role::all();
        $branches = Branch::where('is_active', true)->get();

        return view('users.create', compact('roles', 'branches'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-users');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'main_branch_id' => ['nullable', 'exists:branches,id'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['exists:branches,id'],
            'is_active' => ['boolean'],
        ]);

        // Guard against assigning system-admin if actor is not system-admin
        if (in_array('system-admin', $validated['roles']) && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'main_branch_id' => $validated['main_branch_id'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        $user->syncRoles($validated['roles']);

        if (!empty($validated['branches'])) {
            $user->branches()->sync($validated['branches']);
        }

        ActivityLog::log(
            'user_created',
            $user,
            "Created user {$user->name} ({$user->email})",
            ['roles' => $validated['roles'], 'is_active' => $user->is_active]
        );

        return redirect()->route('users.index')->with('success', __('users.created_successfully'));
    }

    public function show($locale, User $user)
    {
        $this->authorize('view-users');

        $user->load(['roles', 'mainBranch', 'branches', 'activityLogs' => function ($q) {
            $q->latest()->take(20);
        }]);

        return view('users.show', compact('user'));
    }

    public function edit($locale, User $user)
    {
        $this->authorize('edit-users');

        // Prevent non-system-admin from editing system-admin user
        if ($user->hasRole('system-admin') && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $roles = Role::all();
        $branches = Branch::where('is_active', true)->get();

        return view('users.edit', compact('user', 'roles', 'branches'));
    }

    public function update(Request $request, $locale, User $user)
    {
        $this->authorize('edit-users');

        if ($user->hasRole('system-admin') && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'roles' => ['required', 'array'],
            'roles.*' => ['string', 'exists:roles,name'],
            'main_branch_id' => ['nullable', 'exists:branches,id'],
            'branches' => ['nullable', 'array'],
            'branches.*' => ['exists:branches,id'],
            'is_active' => ['boolean'],
        ]);

        if (in_array('system-admin', $validated['roles']) && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->main_branch_id = $validated['main_branch_id'] ?? null;

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        // Prevent self-deactivation
        if (auth()->id() === $user->id && !$request->boolean('is_active')) {
            return back()->with('error', __('users.cannot_deactivate_self'));
        }

        $user->is_active = $request->boolean('is_active');
        $user->save();

        $user->syncRoles($validated['roles']);
        $user->branches()->sync($validated['branches'] ?? []);

        ActivityLog::log(
            'user_updated',
            $user,
            "Updated user {$user->name} ({$user->email})",
            ['roles' => $validated['roles'], 'is_active' => $user->is_active]
        );

        return redirect()->route('users.index')->with('success', __('users.updated_successfully'));
    }

    public function toggleStatus(Request $request, $locale, User $user)
    {
        $this->authorize('toggle-user-status');

        if (auth()->id() === $user->id) {
            return back()->with('error', __('users.cannot_deactivate_self'));
        }

        if ($user->hasRole('system-admin') && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $user->is_active = !$user->is_active;
        $user->save();

        ActivityLog::log(
            'status_toggled',
            $user,
            "Toggled status for user {$user->name} to " . ($user->is_active ? 'Active' : 'Inactive'),
            ['is_active' => $user->is_active]
        );

        return back()->with('success', __('users.status_updated_successfully'));
    }
}
