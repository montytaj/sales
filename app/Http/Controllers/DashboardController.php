<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\PurchaseOrder;
use App\Models\PurchaseInvoice;
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
            $totalSales = (float) Invoice::where('status', '!=', 'cancelled')->sum('total_amount');
            $invoicesCount = Invoice::where('status', '!=', 'cancelled')->count();

            $totalPurchasesInvoices = (float) PurchaseInvoice::where('status', '!=', 'cancelled')
                ->sum(DB::raw('COALESCE(NULLIF(net_amount, 0), total_amount)'));
            $totalPurchaseOrders = (float) PurchaseOrder::whereNotIn('status', ['cancelled'])
                ->whereDoesntHave('invoices')
                ->sum(DB::raw('COALESCE(NULLIF(net_amount, 0), total_amount)'));
            $totalPurchases = $totalPurchasesInvoices + $totalPurchaseOrders;

            $purchasesInvoiceCount = PurchaseInvoice::where('status', '!=', 'cancelled')->count();
            $poCount = PurchaseOrder::whereNotIn('status', ['cancelled'])->whereDoesntHave('invoices')->count();
            $purchasesCount = $purchasesInvoiceCount + $poCount;

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
                    'date' => $inv->invoice_date ? $inv->invoice_date->format('Y-m-d') : ($inv->created_at ? $inv->created_at->format('Y-m-d') : '-'),
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

                $mSales = Invoice::where('status', '!=', 'cancelled')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum('total_amount');

                $mPInvoices = PurchaseInvoice::where('status', '!=', 'cancelled')
                    ->where(function($q) use ($date) {
                        $q->where(function($q2) use ($date) {
                            $q2->whereNotNull('invoice_date')
                               ->whereYear('invoice_date', $date->year)
                               ->whereMonth('invoice_date', $date->month);
                        })->orWhere(function($q3) use ($date) {
                            $q3->whereNull('invoice_date')
                               ->whereYear('created_at', $date->year)
                               ->whereMonth('created_at', $date->month);
                        });
                    })
                    ->sum(DB::raw('COALESCE(NULLIF(net_amount, 0), total_amount)'));

                $mPOrders = PurchaseOrder::whereNotIn('status', ['cancelled'])
                    ->whereDoesntHave('invoices')
                    ->whereYear('created_at', $date->year)
                    ->whereMonth('created_at', $date->month)
                    ->sum(DB::raw('COALESCE(NULLIF(net_amount, 0), total_amount)'));

                $salesMonthly[] = (float) $mSales;
                $purchasesMonthly[] = (float) ($mPInvoices + $mPOrders);
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

