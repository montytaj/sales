<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PermissionController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of system permissions grouped by module.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(401);
        }
        if (!$user->hasRole('system-admin') && !$user->can('manage-permissions') && !$user->can('manage-roles') && !$user->can('view-users')) {
            abort(403, 'غير مصرح بالوصول إلى دليل الصلاحيات');
        }

        $query = Permission::withCount('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name', 'like', "%{$search}%");
        }

        $allPermissions = $query->get();

        $groupMapping = [
            'users' => ['view-dashboard', 'view-users', 'create-users', 'edit-users', 'toggle-user-status', 'assign-roles', 'view-activity-logs', 'manage-roles', 'manage-permissions'],
            'branches_settings' => ['view-branches', 'create-branches', 'edit-branches', 'toggle-branch-status', 'manage-settings'],
            'contacts' => ['view-customers', 'create-customers', 'edit-customers', 'delete-customers', 'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers', 'view-services', 'create-services', 'edit-services', 'delete-services'],
            'sales' => ['view-customer-orders', 'create-customer-orders', 'edit-customer-orders', 'delete-customer-orders', 'view-quotations', 'create-quotations', 'edit-quotations', 'approve-quotations', 'delete-quotations', 'convert-quotation-to-invoice', 'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices', 'print-sales-documents'],
            'finance' => ['view-cashboxes', 'create-cashboxes', 'edit-cashboxes', 'manage-cashbox-shifts', 'view-payment-vouchers', 'create-payment-vouchers', 'cancel-payment-vouchers', 'print-payment-receipts', 'view-cheques', 'manage-cheques', 'view-costing', 'view-accounting', 'manage-accounting'],
            'workshop' => ['view-work-orders', 'create-work-orders', 'edit-work-orders', 'authorize-work-order-start', 'override-work-order-start', 'execute-work-orders', 'deliver-work-orders'],
            'projects' => ['view-surveys', 'create-surveys', 'edit-surveys', 'view-contracts', 'create-contracts', 'edit-contracts', 'approve-contracts', 'view-projects', 'create-projects', 'edit-projects', 'manage-projects', 'view-signage', 'create-signage', 'edit-signage', 'manage-signage'],
            'inventory_purchases' => ['manage-inventory', 'view-warehouse-transfers', 'create-warehouse-transfers', 'approve-warehouse-transfers', 'delete-warehouse-transfers', 'view-purchases', 'create-purchases', 'manage-purchases'],
            'reports' => ['reports.access', 'reports.sales.view', 'reports.financial.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.workshop.view', 'reports.cnc.view', 'reports.projects.view', 'reports.signage.view', 'reports.inventory.view', 'reports.purchases.view', 'reports.accounting.view', 'reports.profitability.view', 'reports.costs.view', 'reports.export_excel', 'reports.export_pdf', 'reports.print', 'reports.view_all_branches', 'reports.view_sensitive_financial_data'],
        ];

        $groupedPermissions = [];

        foreach ($groupMapping as $groupKey => $permissionNames) {
            $filtered = $allPermissions->whereIn('name', $permissionNames);

            if ($request->filled('module') && $request->input('module') !== $groupKey) {
                continue;
            }

            if ($filtered->count() > 0) {
                $groupedPermissions[$groupKey] = $filtered;
            }
        }

        // Uncategorized check
        $categorizedNames = collect($groupMapping)->flatten()->toArray();
        $uncategorized = $allPermissions->whereNotIn('name', $categorizedNames);

        if ($uncategorized->count() > 0 && (!$request->filled('module') || $request->input('module') === 'other')) {
            $groupedPermissions['other'] = $uncategorized;
        }

        return view('permissions.index', compact('groupedPermissions', 'groupMapping', 'allPermissions'));
    }
}
