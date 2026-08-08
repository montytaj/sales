<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\ActivityLog;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    use AuthorizesRequests;

    private function checkRoleAccess(string $permission = 'manage-roles')
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }
        if ($user->hasRole('system-admin')) {
            return true;
        }
        if ($user->can($permission) || $user->can('manage-roles') || $user->can('assign-roles')) {
            return true;
        }
        abort(403, 'غير مصرح بالوصول إلى إدارة الأدوار والصلاحيات');
    }

    /**
     * Display a listing of job roles.
     */
    public function index(Request $request)
    {
        $this->checkRoleAccess('manage-roles');

        $query = Role::withCount(['permissions', 'users']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $roles = $query->latest('id')->paginate(12)->withQueryString();

        return view('roles.index', compact('roles'));
    }

    /**
     * Show form for creating a new role.
     */
    public function create()
    {
        $this->checkRoleAccess('manage-roles');

        $groupedPermissions = $this->getGroupedPermissions();

        return view('roles.create', compact('groupedPermissions'));
    }

    /**
     * Store a newly created role in storage.
     */
    public function store(Request $request)
    {
        $this->checkRoleAccess('manage-roles');

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name', 'regex:/^[a-z0-9\-]+$/i'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ], [
            'name.regex' => 'اسم الدور المعرف يجب أن يتكون من أحرف إنجليزية وأرقام وشرطات فقط بدون مسافات (مثال: sales-rep).',
        ]);

        $role = Role::create([
            'name' => strtolower($validated['name']),
            'guard_name' => 'web',
        ]);

        if (!empty($validated['permissions'])) {
            $role->syncPermissions($validated['permissions']);
        }

        ActivityLog::log(
            'role_created',
            $role,
            "Created job role {$role->name}",
            ['permissions_count' => count($validated['permissions'] ?? [])]
        );

        return redirect()->route('roles.index')->with('success', __('roles.created_successfully'));
    }

    /**
     * Show form for editing a role.
     */
    public function edit($locale, Role $role)
    {
        $this->checkRoleAccess('manage-roles');

        // Prevent non-system-admin from editing system-admin role
        if ($role->name === 'system-admin' && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $role->load('permissions');
        $rolePermissions = $role->permissions->pluck('name')->toArray();
        $groupedPermissions = $this->getGroupedPermissions();

        return view('roles.edit', compact('role', 'rolePermissions', 'groupedPermissions'));
    }

    /**
     * Update the specified role.
     */
    public function update(Request $request, $locale, Role $role)
    {
        $this->checkRoleAccess('manage-roles');

        if ($role->name === 'system-admin' && !auth()->user()->hasRole('system-admin')) {
            abort(403, __('users.cannot_edit_higher_role'));
        }

        $isSystemRole = in_array($role->name, ['system-admin', 'general-manager', 'accountant', 'storekeeper', 'workshop-manager']);

        $rules = [
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:permissions,name'],
        ];

        if (!$isSystemRole) {
            $rules['name'] = ['required', 'string', 'max:100', Rule::unique('roles')->ignore($role->id), 'regex:/^[a-z0-9\-]+$/i'];
        }

        $validated = $request->validate($rules);

        if (!$isSystemRole && isset($validated['name'])) {
            $role->name = strtolower($validated['name']);
            $role->save();
        }

        // If system-admin role, ensure it keeps all permissions
        if ($role->name === 'system-admin') {
            $role->syncPermissions(Permission::all());
        } else {
            $role->syncPermissions($validated['permissions'] ?? []);
        }

        ActivityLog::log(
            'role_updated',
            $role,
            "Updated job role {$role->name}",
            ['permissions_count' => count($validated['permissions'] ?? [])]
        );

        return redirect()->route('roles.index')->with('success', __('roles.updated_successfully'));
    }

    /**
     * Remove the specified role.
     */
    public function destroy($locale, Role $role)
    {
        $this->checkRoleAccess('manage-roles');

        $systemRoles = ['system-admin', 'general-manager', 'branch-manager', 'sales-rep', 'accountant', 'treasurer', 'workshop-manager', 'cnc-operator', 'project-manager', 'storekeeper', 'observer'];

        if (in_array($role->name, $systemRoles)) {
            return back()->with('error', __('roles.cannot_delete_system_role'));
        }

        if ($role->users()->count() > 0) {
            return back()->with('error', __('roles.cannot_delete_role_with_users'));
        }

        $roleName = $role->name;
        $role->delete();

        ActivityLog::log(
            'role_deleted',
            null,
            "Deleted job role {$roleName}"
        );

        return redirect()->route('roles.index')->with('success', __('roles.deleted_successfully'));
    }

    /**
     * Display the Interactive Role-Permission Matrix.
     */
    public function matrix()
    {
        $this->checkRoleAccess('manage-permissions');

        $roles = Role::with('permissions')->orderBy('id')->get();
        $groupedPermissions = $this->getGroupedPermissions();

        return view('roles.matrix', compact('roles', 'groupedPermissions'));
    }

    /**
     * Mass update permissions per role from the matrix.
     */
    public function updateMatrix(Request $request)
    {
        $this->checkRoleAccess('manage-permissions');

        $matrix = $request->input('matrix', []);
        $allRoles = Role::all();

        foreach ($allRoles as $role) {
            // Protect system-admin from losing permissions
            if ($role->name === 'system-admin') {
                $role->syncPermissions(Permission::all());
                continue;
            }

            // Non-system admin user cannot edit system-admin role
            if ($role->name === 'system-admin' && !auth()->user()->hasRole('system-admin')) {
                continue;
            }

            $permissionIds = isset($matrix[$role->id]) ? array_keys($matrix[$role->id]) : [];
            $permissions = Permission::whereIn('id', $permissionIds)->pluck('name')->toArray();

            $role->syncPermissions($permissions);
        }

        ActivityLog::log(
            'permission_matrix_updated',
            null,
            "Updated Role-Permission assignment matrix"
        );

        return redirect()->route('roles.matrix')->with('success', __('roles.matrix_updated_successfully'));
    }

    /**
     * Helper to group permissions by functional module.
     */
    private function getGroupedPermissions()
    {
        $permissions = Permission::all();

        $groupMapping = [
            'users' => ['view-dashboard', 'view-users', 'create-users', 'edit-users', 'toggle-user-status', 'assign-roles', 'view-activity-logs', 'manage-roles', 'manage-permissions'],
            'branches_settings' => ['view-branches', 'create-branches', 'edit-branches', 'toggle-branch-status', 'manage-settings'],
            'contacts' => ['view-customers', 'create-customers', 'edit-customers', 'delete-customers', 'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers', 'view-services', 'create-services', 'edit-services', 'delete-services'],
            'sales' => ['view-customer-orders', 'create-customer-orders', 'edit-customer-orders', 'delete-customer-orders', 'view-quotations', 'create-quotations', 'edit-quotations', 'approve-quotations', 'delete-quotations', 'convert-quotation-to-invoice', 'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices', 'print-sales-documents'],
            'finance' => ['view-cashboxes', 'create-cashboxes', 'edit-cashboxes', 'manage-cashbox-shifts', 'view-payment-vouchers', 'create-payment-vouchers', 'cancel-payment-vouchers', 'print-payment-receipts', 'view-cheques', 'manage-cheques', 'view-costing', 'view-accounting', 'manage-accounting'],
            'workshop' => ['view-work-orders', 'create-work-orders', 'edit-work-orders', 'authorize-work-order-start', 'override-work-order-start', 'execute-work-orders', 'deliver-work-orders'],
            'projects' => ['view-surveys', 'create-surveys', 'edit-surveys', 'view-contracts', 'create-contracts', 'edit-contracts', 'approve-contracts', 'view-projects', 'create-projects', 'edit-projects', 'manage-projects', 'view-signage', 'create-signage', 'edit-signage', 'manage-signage'],
            'inventory_purchases' => ['manage-inventory', 'view-purchases', 'create-purchases', 'manage-purchases'],
            'reports' => ['reports.access', 'reports.sales.view', 'reports.financial.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.workshop.view', 'reports.cnc.view', 'reports.projects.view', 'reports.signage.view', 'reports.inventory.view', 'reports.purchases.view', 'reports.accounting.view', 'reports.profitability.view', 'reports.costs.view', 'reports.export_excel', 'reports.export_pdf', 'reports.print', 'reports.view_all_branches', 'reports.view_sensitive_financial_data'],
        ];

        $grouped = [];

        foreach ($groupMapping as $groupKey => $permissionNames) {
            $grouped[$groupKey] = $permissions->whereIn('name', $permissionNames);
        }

        // Catch any uncategorized permissions
        $categorizedNames = collect($groupMapping)->flatten()->toArray();
        $uncategorized = $permissions->whereNotIn('name', $categorizedNames);

        if ($uncategorized->count() > 0) {
            $grouped['other'] = $uncategorized;
        }

        return $grouped;
    }
}
