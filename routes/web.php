<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\QuotationController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\CashboxController;
use App\Http\Controllers\PaymentVoucherController;
use App\Http\Controllers\ChequeController;
use App\Http\Controllers\UnitController;
use App\Http\Controllers\ItemCategoryController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\InventoryItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\WorkOrderController;
use Illuminate\Support\Facades\Route;

// Redirect unlocalized root / and /login to default Arabic locale /ar/login
Route::get('/', function () {
    return redirect('/ar/login');
});

Route::get('/login', function () {
    return redirect('/ar/login');
});

Route::post('/login', [App\Http\Controllers\Auth\AuthenticatedSessionController::class, 'store']);

use App\Http\Controllers\DashboardController;

// Group routes under {locale} parameter for ar and en
Route::group([
    'prefix' => '{locale}',
    'where' => ['locale' => 'ar|en'],
    'middleware' => ['setLocale', 'localeSessionRedirect', 'localizationRedirect', 'localeViewPath']
], function () {

    Route::get('/', function ($locale) {
        return auth()->check()
            ? redirect()->route('dashboard', ['locale' => $locale])
            : redirect()->route('login', ['locale' => $locale]);
    });

    // Public System Guide & Feature Manual Route (Accessible to everyone)
    Route::get('/guide', [\App\Http\Controllers\SystemGuideController::class, 'index'])->name('system-guide');

    require __DIR__.'/auth.php';

    Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

    Route::middleware('auth')->group(function () {
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // User Management Routes
        Route::resource('users', UserController::class);
        Route::patch('users/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');

        // Roles & Permissions Management Routes
        Route::get('roles/matrix', [RoleController::class, 'matrix'])->name('roles.matrix');
        Route::post('roles/matrix', [RoleController::class, 'updateMatrix'])->name('roles.matrix.update');
        Route::resource('roles', RoleController::class);
        Route::resource('permissions', PermissionController::class)->only(['index']);

        // Branch Management Routes
        Route::resource('branches', BranchController::class);
        Route::patch('branches/{branch}/toggle-status', [BranchController::class, 'toggleStatus'])->name('branches.toggle-status');

        // Settings Routes
        Route::get('settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');

        // Customers Routes
        Route::resource('customers', CustomerController::class);
        Route::post('customers/{customer}/attachments', [CustomerController::class, 'uploadAttachment'])->name('customers.upload-attachment');

        // Suppliers Routes
        Route::resource('suppliers', SupplierController::class);
        Route::post('suppliers/{supplier}/attachments', [SupplierController::class, 'uploadAttachment'])->name('suppliers.upload-attachment');

        // Services Catalog Routes
        Route::resource('services', \App\Http\Controllers\ServiceController::class);

        // Signage Orders Routes
        Route::resource('signage-orders', \App\Http\Controllers\SignageOrderController::class);
        Route::post('signage-orders/{signageOrder}/approve-design', [\App\Http\Controllers\SignageOrderController::class, 'approveDesign'])->name('signage-orders.approve-design');

        // Site Surveys Routes
        Route::resource('surveys', \App\Http\Controllers\SiteSurveyController::class);

        // Lookups & Master Data Routes
        Route::resource('units', UnitController::class);
        Route::resource('categories', ItemCategoryController::class);
        Route::resource('warehouses', WarehouseController::class);

        // Inventory Items & Movements Routes
        Route::resource('inventory', InventoryItemController::class);
        Route::post('inventory/scraps', [\App\Http\Controllers\InventoryController::class, 'storeScrap'])->name('inventory.scraps.store');

        // Quotations Routes
        Route::resource('quotations', QuotationController::class);
        Route::patch('quotations/{quotation}/approve', [QuotationController::class, 'approve'])->name('quotations.approve');
        Route::post('quotations/{quotation}/convert-to-invoice', [QuotationController::class, 'convertToInvoice'])->name('quotations.convert-to-invoice');
        Route::get('quotations/{quotation}/print', [QuotationController::class, 'print'])->name('quotations.print');

        // POS Cashier Routes
        Route::get('pos', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
        Route::post('pos', [\App\Http\Controllers\PosController::class, 'store'])->name('pos.store');

        // Sales Invoices Routes
        Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::patch('invoices/{invoice}/update-status', [InvoiceController::class, 'updateStatus'])->name('invoices.update-status');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');

        // Purchases & Purchase Orders Routes
        Route::get('purchases', [PurchaseController::class, 'index'])->name('purchases.index');
        Route::get('purchases/create-invoice', [PurchaseController::class, 'createInvoice'])->name('purchases.create_invoice');
        Route::post('purchases/store-invoice', [PurchaseController::class, 'storeInvoice'])->name('purchases.store_invoice');
        Route::post('purchases/store-po', [PurchaseController::class, 'storePo'])->name('purchases.store_po');
        Route::post('purchases/orders/{po}/receive-goods', [PurchaseController::class, 'receiveGoods'])->name('purchases.receive_goods');
        Route::get('purchases/{invoice}/show-invoice', [PurchaseController::class, 'showInvoice'])->name('purchases.show_invoice');

        // Cashboxes Routes
        Route::resource('cashboxes', CashboxController::class);
        Route::post('cashboxes/{cashbox}/open-shift', [CashboxController::class, 'openShift'])->name('cashboxes.open-shift');
        Route::post('cashboxes/{cashbox}/close-shift', [CashboxController::class, 'closeShift'])->name('cashboxes.close-shift');
        Route::post('cashboxes/{cashbox}/transfer', [CashboxController::class, 'transfer'])->name('cashboxes.transfer');

        // Payment Vouchers Routes
        Route::resource('payments', PaymentVoucherController::class);
        Route::patch('payments/{payment}/cancel', [PaymentVoucherController::class, 'cancel'])->name('payments.cancel');
        Route::get('payments/{payment}/print', [PaymentVoucherController::class, 'print'])->name('payments.print');

        // Cheques Routes
        Route::resource('cheques', ChequeController::class)->only(['index', 'show']);
        Route::patch('cheques/{cheque}/update-status', [ChequeController::class, 'updateStatus'])->name('cheques.update-status');

        // 5-Level Chart of Accounts Routes
        Route::get('accounting', [AccountController::class, 'index'])->name('accounting.index');
        Route::resource('accounting', AccountController::class)->except(['index']);

        // Projects, Contracts & Work Orders Routes
        Route::resource('contracts', \App\Http\Controllers\ContractController::class);
        Route::post('contracts/{contract}/approve', [\App\Http\Controllers\ContractController::class, 'approve'])->name('contracts.approve');
        Route::post('contracts/{contract}/convert-to-project', [\App\Http\Controllers\ContractController::class, 'convertToProject'])->name('contracts.convert-to-project');

        Route::resource('projects', ProjectController::class);
        Route::resource('work-orders', WorkOrderController::class);
        Route::post('work-orders/{workOrder}/authorize-start', [WorkOrderController::class, 'authorizeStart'])->name('work-orders.authorize-start');
        Route::post('work-orders/{workOrder}/override-start', [WorkOrderController::class, 'overrideStart'])->name('work-orders.override-start');
        Route::post('work-orders/{workOrder}/deliver', [WorkOrderController::class, 'deliver'])->name('work-orders.deliver');

        // Workshop Kiosk Routes
        Route::get('workshop-kiosk', [\App\Http\Controllers\WorkshopKioskController::class, 'index'])->name('workshop-kiosk.index');
        Route::post('workshop-kiosk/{workOrder}/action', [\App\Http\Controllers\WorkshopKioskController::class, 'action'])->name('workshop-kiosk.action');

        // Notifications & Reports
        Route::get('notifications', [NotificationController::class, 'index'])->name('notifications.index');
        Route::get('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
        Route::patch('notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-as-read');
        Route::post('notifications/mark-all-as-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-as-read');

        Route::get('reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('reports/customer-statement', [ReportController::class, 'customerStatement'])->name('reports.customer-statement');
        Route::get('reports/supplier-statement', [ReportController::class, 'supplierStatement'])->name('reports.supplier-statement');
        Route::get('reports/sales', [ReportController::class, 'sales'])->name('reports.sales');
        Route::get('reports/workshop', [ReportController::class, 'workshop'])->name('reports.workshop');
        Route::get('reports/projects', [ReportController::class, 'projects'])->name('reports.projects');
        Route::get('reports/financial', [ReportController::class, 'financial'])->name('reports.financial');
        Route::get('reports/inventory', [ReportController::class, 'inventory'])->name('reports.inventory');
        Route::get('activity-logs', [\App\Http\Controllers\ActivityLogController::class, 'index'])->name('activity-logs.index');

        // Multi-Currency & Exchange Rates Routes
        Route::get('currencies', [\App\Http\Controllers\CurrencyController::class, 'index'])->name('currencies.index');
        Route::post('currencies', [\App\Http\Controllers\CurrencyController::class, 'store'])->name('currencies.store');
        Route::put('currencies/{currency}', [\App\Http\Controllers\CurrencyController::class, 'update'])->name('currencies.update');
        Route::post('currencies/{currency}/set-base', [\App\Http\Controllers\CurrencyController::class, 'setBase'])->name('currencies.setBase');
        Route::delete('currencies/{currency}', [\App\Http\Controllers\CurrencyController::class, 'destroy'])->name('currencies.destroy');
    });
});
