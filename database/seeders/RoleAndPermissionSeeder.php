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

        // 3. Assign Permissions to Roles

        // General Manager (المدير العام) -> All Permissions
        $generalManager = Role::findByName('general-manager', 'web');
        $generalManager->givePermissionTo(Permission::all());

        // System Admin (مدير النظام) -> All Permissions
        $systemAdmin = Role::findByName('system-admin', 'web');
        $systemAdmin->givePermissionTo(Permission::all());

        // Branch Manager (مدير الفرع)
        $branchManager = Role::findByName('branch-manager', 'web');
        $branchManager->givePermissionTo([
            'view-dashboard', 'view-users', 'view-branches',
            'view-customers', 'create-customers', 'edit-customers',
            'view-suppliers', 'create-suppliers', 'edit-suppliers',
            'view-services', 'view-customer-orders', 'create-customer-orders', 'edit-customer-orders',
            'view-quotations', 'create-quotations', 'edit-quotations', 'approve-quotations', 'convert-quotation-to-invoice',
            'view-invoices', 'create-invoices', 'edit-invoices', 'print-sales-documents',
            'view-cashboxes', 'manage-cashbox-shifts', 'view-payment-vouchers', 'create-payment-vouchers', 'print-payment-receipts', 'view-cheques',
            'view-work-orders', 'create-work-orders', 'edit-work-orders', 'authorize-work-order-start', 'execute-work-orders', 'deliver-work-orders',
            'view-surveys', 'create-surveys', 'edit-surveys', 'view-contracts', 'create-contracts', 'edit-contracts', 'approve-contracts',
            'view-projects', 'create-projects', 'edit-projects', 'manage-projects', 'view-signage', 'create-signage', 'edit-signage', 'manage-signage',
            'manage-inventory', 'view-warehouse-transfers', 'create-warehouse-transfers', 'approve-warehouse-transfers',
            'view-purchases', 'create-purchases', 'manage-purchases', 'view-costing',
            'reports.access', 'reports.sales.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.workshop.view', 'reports.projects.view', 'reports.inventory.view', 'reports.purchases.view', 'reports.print', 'reports.export_excel'
        ]);

        // Sales Rep (موظف مبيعات)
        $salesRep = Role::findByName('sales-rep', 'web');
        $salesRep->givePermissionTo([
            'view-dashboard', 'view-customers', 'create-customers', 'edit-customers',
            'view-services', 'view-customer-orders', 'create-customer-orders', 'edit-customer-orders',
            'view-quotations', 'create-quotations', 'edit-quotations', 'convert-quotation-to-invoice',
            'view-invoices', 'create-invoices', 'print-sales-documents',
            'view-surveys', 'create-surveys', 'edit-surveys', 'view-signage', 'create-signage',
            'reports.access', 'reports.sales.view', 'reports.customers.view', 'reports.print'
        ]);

        // Accountant (محاسب)
        $accountant = Role::findByName('accountant', 'web');
        $accountant->givePermissionTo([
            'view-dashboard', 'view-customers', 'view-suppliers', 'view-invoices',
            'view-cashboxes', 'create-cashboxes', 'edit-cashboxes', 'manage-cashbox-shifts',
            'view-payment-vouchers', 'create-payment-vouchers', 'cancel-payment-vouchers', 'print-payment-receipts',
            'view-cheques', 'manage-cheques', 'view-purchases', 'create-purchases', 'manage-purchases', 'view-costing',
            'view-accounting', 'manage-accounting',
            'reports.access', 'reports.sales.view', 'reports.financial.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.purchases.view', 'reports.accounting.view', 'reports.profitability.view', 'reports.costs.view', 'reports.export_excel', 'reports.export_pdf', 'reports.print', 'reports.view_sensitive_financial_data', 'reports.financial_statements.view', 'reports.balance_sheet.view', 'reports.income_statement.view', 'reports.trial_balance.view', 'reports.cash_flow.view', 'reports.equity_changes.view', 'reports.general_ledger.view', 'reports.account_balances.view'
        ]);

        // Treasurer (أمين خزنة)
        $treasurer = Role::findByName('treasurer', 'web');
        $treasurer->givePermissionTo([
            'view-dashboard', 'view-cashboxes', 'manage-cashbox-shifts',
            'view-payment-vouchers', 'create-payment-vouchers', 'print-payment-receipts',
            'view-cheques', 'manage-cheques',
            'reports.access', 'reports.financial.view', 'reports.print'
        ]);

        // Workshop Manager (مدير ورشة)
        $workshopManager = Role::findByName('workshop-manager', 'web');
        $workshopManager->givePermissionTo([
            'view-dashboard', 'view-services',
            'view-work-orders', 'create-work-orders', 'edit-work-orders', 'authorize-work-order-start', 'override-work-order-start', 'execute-work-orders', 'deliver-work-orders',
            'view-surveys', 'view-signage', 'manage-inventory',
            'reports.access', 'reports.workshop.view', 'reports.cnc.view', 'reports.signage.view', 'reports.inventory.view', 'reports.print'
        ]);

        // CNC Operator (عامل CNC)
        $cncOperator = Role::findByName('cnc-operator', 'web');
        $cncOperator->givePermissionTo([
            'view-dashboard', 'view-work-orders', 'execute-work-orders',
            'reports.access', 'reports.cnc.view'
        ]);

        // Project Manager (مدير مشروع)
        $projectManager = Role::findByName('project-manager', 'web');
        $projectManager->givePermissionTo([
            'view-dashboard', 'view-customers', 'view-services',
            'view-surveys', 'create-surveys', 'edit-surveys',
            'view-contracts', 'create-contracts', 'edit-contracts', 'approve-contracts',
            'view-projects', 'create-projects', 'edit-projects', 'manage-projects',
            'view-signage', 'create-signage', 'edit-signage', 'manage-signage',
            'view-work-orders', 'create-work-orders', 'edit-work-orders', 'deliver-work-orders',
            'reports.access', 'reports.projects.view', 'reports.signage.view', 'reports.workshop.view', 'reports.print'
        ]);

        // Storekeeper (مسؤول مخزن)
        $storekeeper = Role::findByName('storekeeper', 'web');
        $storekeeper->givePermissionTo([
            'view-dashboard', 'manage-inventory',
            'view-warehouse-transfers', 'create-warehouse-transfers', 'approve-warehouse-transfers', 'delete-warehouse-transfers',
            'view-purchases', 'create-purchases', 'manage-purchases',
            'reports.access', 'reports.inventory.view', 'reports.purchases.view', 'reports.print', 'reports.export_excel'
        ]);

        // Observer (مراقب)
        $observer = Role::findByName('observer', 'web');
        $observer->givePermissionTo([
            'view-dashboard', 'view-users', 'view-branches', 'view-customers', 'view-suppliers', 'view-services',
            'view-customer-orders', 'view-quotations', 'view-invoices', 'view-cashboxes', 'view-payment-vouchers', 'view-cheques',
            'view-work-orders', 'view-surveys', 'view-contracts', 'view-projects', 'view-signage', 'view-warehouse-transfers',
            'view-purchases', 'view-costing', 'view-accounting',
            'reports.access', 'reports.sales.view', 'reports.customers.view', 'reports.suppliers.view', 'reports.workshop.view', 'reports.cnc.view', 'reports.projects.view', 'reports.signage.view', 'reports.inventory.view', 'reports.purchases.view', 'reports.print'
        ]);

        // 4. Assign system-admin role to default admin user
        $adminUser = User::where('email', 'admin@example.com')->first();
        if ($adminUser) {
            $adminUser->assignRole('system-admin');
        }
    }
}
