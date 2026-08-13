<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Cache;
use App\Models\Invoice;
use App\Models\PurchaseInvoice;
use App\Models\PurchaseOrder;
use App\Models\InventoryItem;
use App\Models\Customer;
use App\Models\Supplier;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        URL::defaults(['locale' => app()->getLocale()]);

        // Grant system-admin role all permissions across the entire application
        Gate::before(function ($user, $ability) {
            return $user->hasRole('system-admin') ? true : null;
        });

        // Cache Invalidation Listeners for High-Performance Query Caching
        $invalidateDashboardCache = function () {
            Cache::forget('dashboard_executive_summary');
        };

        Invoice::saved($invalidateDashboardCache);
        Invoice::deleted($invalidateDashboardCache);
        PurchaseInvoice::saved($invalidateDashboardCache);
        PurchaseInvoice::deleted($invalidateDashboardCache);
        PurchaseOrder::saved($invalidateDashboardCache);
        PurchaseOrder::deleted($invalidateDashboardCache);
        InventoryItem::saved($invalidateDashboardCache);
        InventoryItem::deleted($invalidateDashboardCache);
        Customer::saved($invalidateDashboardCache);
        Customer::deleted($invalidateDashboardCache);
        Supplier::saved($invalidateDashboardCache);
        Supplier::deleted($invalidateDashboardCache);
    }
}
