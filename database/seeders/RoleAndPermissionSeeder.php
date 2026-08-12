<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use App\Models\User;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // 1. Create Permissions
        $permissions = [
            'view-dashboard',
            'view-users', 'create-users', 'edit-users', 'toggle-user-status', 'assign-roles', 'view-activity-logs',
            'view-branches', 'create-branches', 'edit-branches', 'toggle-branch-status',
            'manage-settings',
            // Customers & Suppliers & Services
            'view-customers', 'create-customers', 'edit-customers', 'delete-customers',
            'view-suppliers', 'create-suppliers', 'edit-suppliers', 'delete-suppliers',
            'view-services', 'create-services', 'edit-services', 'delete-services',
            // Sales & Quotations & Invoices
            'view-customer-orders', 'create-customer-orders', 'edit-customer-orders', 'delete-customer-orders',
            'view-quotations', 'create-quotations', 'edit-quotations', 'approve-quotations', 'delete-quotations', 'convert-quotation-to-invoice',
            'view-invoices', 'create-invoices', 'edit-invoices', 'delete-invoices', 'print-sales-documents',
            // Cashboxes & Shifts & Payments & Cheques
            'view-cashboxes', 'create-cashboxes', 'edit-cashboxes', 'manage-cashbox-shifts',
            'view-payment-vouchers', 'create-payment-vouchers', 'cancel-payment-vouchers', 'print-payment-receipts',
            'view-cheques', 'manage-cheques',
            // Work Orders & Workshop Execution
            'view-work-orders', 'create-work-orders', 'edit-work-orders',
            'authorize-work-order-start', 'override-work-order-start',
            'execute-work-orders', 'deliver-work-orders',
            // Surveys & Contracts & Projects & Signage
            'view-surveys', 'create-surveys', 'edit-surveys',
            'view-contracts', 'create-contracts', 'edit-contracts', 'approve-contracts',
            'view-projects', 'create-projects', 'edit-projects', 'manage-projects',
            'view-signage', 'create-signage', 'edit-signage', 'manage-signage',
            // Inventory, Purchases, Costing & Accounting
            'manage-inventory',
            'view-warehouse-transfers', 'create-warehouse-transfers', 'approve-warehouse-transfers', 'delete-warehouse-transfers',
            'view-purchases', 'create-purchases', 'manage-purchases',
            'view-costing',
            'view-accounting', 'manage-accounting',
            // Detailed Reporting Module Permissions
            'reports.access',
            'reports.sales.view',
            'reports.financial.view',
            'reports.customers.view',
            'reports.suppliers.view',
            'reports.workshop.view',
            'reports.cnc.view',
            'reports.projects.view',
            'reports.signage.view',
            'reports.inventory.view',
            'reports.purchases.view',
            'reports.accounting.view',
            'reports.profitability.view',
            'reports.costs.view',
            'reports.export_excel',
            'reports.export_pdf',
            'reports.print',
            'reports.view_all_branches',
            'reports.view_sensitive_financial_data',
            'reports.financial_statements.view',
            'reports.balance_sheet.view',
            'reports.income_statement.view',
            'reports.trial_balance.view',
            'reports.cash_flow.view',
            'reports.equity_changes.view',
            'reports.general_ledger.view',
            'reports.account_balances.view',
        ];


        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate(['name' => $permissionName, 'guard_name' => 'web']);
        }

        // 2. Define 11 Roles
        $roles = [
            'system-admin' => 'مدير النظام',
            'general-manager' => 'المدير العام',
            'branch-manager' => 'مدير الفرع',
            'sales-rep' => 'موظف مبيعات',
            'accountant' => 'محاسب',
            'treasurer' => 'أمين خزنة',
            'workshop-manager' => 'مدير ورشة',
            'cnc-operator' => 'عامل CNC',
            'project-manager' => 'مدير مشروع',
            'storekeeper' => 'مسؤول مخزن',
            'observer' => 'مراقب',
        ];

        foreach ($roles as $roleKey => $displayName) {
            Role::firstOrCreate(['name' => $roleKey, 'guard_name' => 'web']);
        }

        // 3. Assign All Permissions to system-admin
        $systemAdmin = Role::findByName('system-admin', 'web');
        $systemAdmin->givePermissionTo(Permission::all());

        // Assign Accountant permissions
        $accountant = Role::findByName('accountant', 'web');
        $accountant->givePermissionTo([
            'view-dashboard', 'view-invoices', 'view-payment-vouchers', 'create-payment-vouchers',
            'view-cheques', 'manage-cheques', 'view-purchases', 'view-costing',
            'view-accounting', 'manage-accounting'
        ]);

        // Assign Storekeeper permissions
        $storekeeper = Role::findByName('storekeeper', 'web');
        $storekeeper->givePermissionTo([
            'view-dashboard', 'manage-inventory', 'view-warehouse-transfers', 'create-warehouse-transfers', 'approve-warehouse-transfers', 'view-purchases'
        ]);

        // 4. Assign system-admin role to default admin user
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('system-admin');
        }
    }
}
