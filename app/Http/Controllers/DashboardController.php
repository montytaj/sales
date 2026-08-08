<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index($locale = 'ar')
    {
        $dashboardData = Cache::remember('dashboard_executive_summary', 300, function () {
            $totalSales = (float) Invoice::sum('total_amount');
            $invoicesCount = Invoice::count();

            $totalPurchases = (float) PurchaseOrder::sum('total_amount');
            $purchasesCount = PurchaseOrder::count();

            $customersCount = Customer::where('is_active', true)->count();
            $suppliersCount = Supplier::where('is_active', true)->count();

            $warehousesCount = Warehouse::where('is_active', true)->count();
            $itemsCount = InventoryItem::where('is_active', true)->count();

            $recentInvoices = Invoice::with('customer')->latest()->take(6)->get()->map(function($inv) {
                return [
                    'id' => $inv->id,
                    'invoice_number' => $inv->invoice_number,
                    'customer' => ['name' => $inv->customer?->name ?? 'عميل نقدي'],
                    'total_amount' => (float) $inv->total_amount,
                    'status' => $inv->status ?? 'pending',
                ];
            })->toArray();

            $lowStockItems = InventoryItem::with(['category', 'baseUnit', 'warehouseItems'])
                ->where('is_active', true)
                ->latest()
                ->take(6)
                ->get()
                ->map(function($item) {
                    return [
                        'id' => $item->id,
                        'item_code' => $item->item_code,
                        'name' => $item->name,
                        'category' => ['name' => $item->category?->name ?? 'عام'],
                        'base_unit' => ['name' => $item->baseUnit?->name ?? 'قطعة'],
                        'warehouse_items' => $item->warehouseItems->toArray(),
                    ];
                })->toArray();

            // 6-Month Sales & Purchases Analytics
            $months = [];
            $salesMonthly = [];
            $purchasesMonthly = [];

            for ($i = 5; $i >= 0; $i--) {
                $date = now()->subMonths($i);
                $monthLabel = $date->translatedFormat('M Y');
                $months[] = $monthLabel;

                $mSales = Invoice::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');

                $mPurchases = PurchaseOrder::whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');

                $salesMonthly[] = (float) $mSales;
                $purchasesMonthly[] = (float) $mPurchases;
            }

            return [
                'totalSales' => $totalSales,
                'invoicesCount' => $invoicesCount,
                'totalPurchases' => $totalPurchases,
                'purchasesCount' => $purchasesCount,
                'customersCount' => $customersCount,
                'suppliersCount' => $suppliersCount,
                'warehousesCount' => $warehousesCount,
                'itemsCount' => $itemsCount,
                'recentInvoices' => $recentInvoices,
                'lowStockItems' => $lowStockItems,
                'months' => $months,
                'salesMonthly' => $salesMonthly,
                'purchasesMonthly' => $purchasesMonthly,
            ];
        });

        return view('dashboard', $dashboardData);
    }
}
