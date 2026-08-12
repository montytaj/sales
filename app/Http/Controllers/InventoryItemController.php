<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class InventoryItemController extends Controller
{
    public function index($locale = 'ar', ?Request $request = null)
    {
        $request = $request ?? request();
        if (!setting('inventory_enabled', true)) {
            return redirect()->route('dashboard', ['locale' => $locale])->with('error', 'وحدة المخزون معطلة حالياً في إعدادات النظام.');
        }

        $warehouses = Warehouse::where('is_active', true)->get();
        $selectedWarehouseId = $request->input('warehouse_id');
        $selectedWarehouse = $selectedWarehouseId ? $warehouses->firstWhere('id', $selectedWarehouseId) : null;

        $query = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems.warehouse']);

        if ($selectedWarehouseId) {
            $query->whereHas('warehouseItems', function ($q) use ($selectedWarehouseId) {
                $q->where('warehouse_id', $selectedWarehouseId);
            });
        }

        $items = $query->latest()->get();

        return view('inventory.index', compact('items', 'warehouses', 'selectedWarehouseId', 'selectedWarehouse'));
    }


    public function create($locale = 'ar')
    {
        $categories = ItemCategory::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('inventory.create', compact('categories', 'units', 'warehouses'));
    }

    public function store($locale, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:inventory_items,code',
            'barcode' => 'nullable|string|max:100|unique:inventory_items,barcode',
            'category_id' => 'nullable|exists:item_categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'wholesale_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'required|numeric|min:1',
            'cost_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $itemCode = $request->code ?: ('ITEM-' . strtoupper(uniqid()));

        $item = InventoryItem::create([
            'name' => $request->name,
            'item_code' => $itemCode,
            'code' => $itemCode,
            'barcode' => $request->barcode,
            'category_id' => $request->category_id,
            'base_unit_id' => $request->base_unit_id,
            'wholesale_unit_id' => $request->wholesale_unit_id,
            'conversion_factor' => $request->conversion_factor,
            'cost_price' => $request->cost_price,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'min_stock_alert' => $request->min_stock_alert ?? 0,
            'default_purchase_price' => $request->cost_price,
            'default_sale_price' => $request->retail_price,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        // Initial stock insertion per warehouse if specified (supporting wholesale & base units)
        if ($request->has('initial_stock') && is_array($request->initial_stock)) {
            $conversionFactor = max(1, (float)($request->conversion_factor ?? 1));
            foreach ($request->initial_stock as $warehouseId => $stockData) {
                $wholesaleQty = 0;
                $baseQty = 0;

                if (is_array($stockData)) {
                    $wholesaleQty = max(0, (float)($stockData['wholesale'] ?? 0));
                    $baseQty = max(0, (float)($stockData['base'] ?? 0));
                } else if (is_numeric($stockData)) {
                    $wholesaleQty = max(0, (float)$stockData);
                }

                $totalQtyInBase = ($wholesaleQty * $conversionFactor) + $baseQty;

                if ($totalQtyInBase > 0) {
                    \App\Models\WarehouseItem::updateOrCreate(
                        ['warehouse_id' => $warehouseId, 'inventory_item_id' => $item->id],
                        ['qty_in_base_units' => $totalQtyInBase]
                    );

                    \App\Models\StockMovement::create([
                        'warehouse_id' => $warehouseId,
                        'item_id' => $item->id,
                        'movement_type' => 'in',
                        'quantity' => $totalQtyInBase,
                        'reference_type' => 'opening_balance',
                        'notes' => 'بضاعة أول المدة (افتتاحي عند إضافة الصنف)',
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }
            }
        }

        return redirect()->route('inventory.index')->with('success', 'تم إضافة الصنف بنجاح وإعداد رصيد بضاعة أول المدة وكروت المخازن.');
    }

    public function show($locale, $id)
    {
        $item = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems.warehouse'])->findOrFail($id);
        return view('inventory.show', compact('item'));
    }

    public function edit($locale, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $categories = ItemCategory::where('is_active', true)->get();
        $units = Unit::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $warehouseItems = WarehouseItem::where('inventory_item_id', $item->id)->pluck('qty_in_base_units', 'warehouse_id')->toArray();

        return view('inventory.edit', compact('item', 'categories', 'units', 'warehouses', 'warehouseItems'));
    }

    public function update($locale, Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:inventory_items,code,' . $item->id,
            'barcode' => 'nullable|string|max:100|unique:inventory_items,barcode,' . $item->id,
            'category_id' => 'nullable|exists:item_categories,id',
            'base_unit_id' => 'required|exists:units,id',
            'wholesale_unit_id' => 'nullable|exists:units,id',
            'conversion_factor' => 'required|numeric|min:1',
            'cost_price' => 'required|numeric|min:0',
            'wholesale_price' => 'required|numeric|min:0',
            'retail_price' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        $item->update([
            'name' => $request->name,
            'code' => $request->code ?: $item->code,
            'barcode' => $request->barcode,
            'category_id' => $request->category_id,
            'base_unit_id' => $request->base_unit_id,
            'wholesale_unit_id' => $request->wholesale_unit_id,
            'conversion_factor' => $request->conversion_factor,
            'cost_price' => $request->cost_price,
            'wholesale_price' => $request->wholesale_price,
            'retail_price' => $request->retail_price,
            'min_stock_alert' => $request->min_stock_alert ?? 0,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        if ($request->has('stock') && is_array($request->stock)) {
            foreach ($request->stock as $warehouseId => $qtyInWholesale) {
                $qtyInBase = $qtyInWholesale * $request->conversion_factor;
                WarehouseItem::updateOrCreate(
                    ['warehouse_id' => $warehouseId, 'inventory_item_id' => $item->id],
                    ['qty_in_base_units' => max(0, $qtyInBase)]
                );
            }
        }

        return redirect()->route('inventory.index')->with('success', 'تم تحديث بيانات الصنف والمخزون بنجاح.');
    }

    public function destroy($locale, $id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();
        return redirect()->route('inventory.index')->with('success', 'تم حذف الصنف بنجاح');
    }

    public function itemCard($locale = 'ar', ?Request $request = null)
    {
        $request = $request ?? request();
        if (!setting('inventory_enabled', true)) {
            return redirect()->route('dashboard', ['locale' => $locale])->with('error', 'وحدة المخزون معطلة حالياً في إعدادات النظام.');
        }

        $allItems = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit'])
            ->orderBy('name')
            ->get();

        $selectedItemId = $request->input('item_id');
        $selectedItem = null;
        $warehouseStock = collect();
        $movements = collect();
        $totalBaseStock = 0;
        $totalValuation = 0;
        $warehousesWithStockCount = 0;
        $stockStatus = 'none';

        $warehouses = Warehouse::with('branch')->where('is_active', true)->get();

        if ($selectedItemId) {
            $selectedItem = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems.warehouse.branch'])
                ->find($selectedItemId);

            if ($selectedItem) {
                $existingWarehouseItems = WarehouseItem::where('inventory_item_id', $selectedItem->id)
                    ->get()
                    ->keyBy('warehouse_id');

                $factor = (float) ($selectedItem->conversion_factor ?? 1);
                $costPrice = (float) ($selectedItem->cost_price ?? 0);

                foreach ($warehouses as $wh) {
                    $wItem = $existingWarehouseItems->get($wh->id);
                    $qtyInBase = $wItem ? (float) $wItem->qty_in_base_units : 0.0;
                    $valuation = $qtyInBase * $costPrice;

                    $totalBaseStock += $qtyInBase;
                    $totalValuation += $valuation;

                    if ($qtyInBase > 0) {
                        $warehousesWithStockCount++;

                        $formattedStock = '-';
                        if ($factor > 1 && $selectedItem->wholesaleUnit && $selectedItem->baseUnit) {
                            $wholesaleQty = floor($qtyInBase / $factor);
                            $remainingBase = fmod($qtyInBase, $factor);
                            $parts = [];
                            if ($wholesaleQty > 0) {
                                $parts[] = "{$wholesaleQty} " . $selectedItem->wholesaleUnit->name;
                            }
                            if ($remainingBase > 0 || $wholesaleQty == 0) {
                                $parts[] = "{$remainingBase} " . $selectedItem->baseUnit->name;
                            }
                            $formattedStock = implode(' و ', $parts);
                        } else {
                            $unitName = $selectedItem->baseUnit?->name ?? $selectedItem->unit ?? 'قطعة';
                            $formattedStock = "{$qtyInBase} {$unitName}";
                        }

                        $warehouseStock->push([
                            'warehouse' => $wh,
                            'qty_in_base' => $qtyInBase,
                            'formatted_stock' => $formattedStock,
                            'valuation' => $valuation,
                        ]);
                    }
                }

                $minAlert = (float) ($selectedItem->min_stock_alert ?? 0);
                if ($totalBaseStock <= 0) {
                    $stockStatus = 'out';
                } elseif ($minAlert > 0 && $totalBaseStock <= $minAlert) {
                    $stockStatus = 'low';
                } else {
                    $stockStatus = 'safe';
                }

                $movementsQuery = StockMovement::with(['warehouse', 'creator'])
                    ->where('item_id', $selectedItem->id);

                if ($request->filled('from_date')) {
                    $movementsQuery->whereDate('created_at', '>=', $request->from_date);
                }
                if ($request->filled('to_date')) {
                    $movementsQuery->whereDate('created_at', '<=', $request->to_date);
                }
                if ($request->filled('warehouse_id')) {
                    $movementsQuery->where('warehouse_id', $request->warehouse_id);
                }
                if ($request->filled('movement_type')) {
                    $movementsQuery->where('movement_type', $request->movement_type);
                }

                $movements = $movementsQuery->latest()->get();

                if ($request->input('export') === 'csv') {
                    $headers = [
                        "Content-type" => "text/csv; charset=UTF-8",
                        "Content-Disposition" => "attachment; filename=item_card_{$selectedItem->code}.csv",
                        "Pragma" => "no-cache",
                        "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                        "Expires" => "0"
                    ];

                    $callback = function () use ($selectedItem, $warehouseStock, $movements, $totalBaseStock, $totalValuation) {
                        $file = fopen('php://output', 'w');
                        fputs($file, "\xEF\xBB\xBF");

                        fputcsv($file, ['كارت جرد ومتابعة الصنف التفصيلي - ERP']);
                        fputcsv($file, ['اسم الصنف:', $selectedItem->name]);
                        fputcsv($file, ['كود الصنف:', $selectedItem->code ?? $selectedItem->item_code]);
                        fputcsv($file, ['البارشود:', $selectedItem->barcode ?? '-']);
                        fputcsv($file, ['التصنيف:', $selectedItem->category?->name ?? '-']);
                        fputcsv($file, ['الوحدة الأساسية:', $selectedItem->baseUnit?->name ?? $selectedItem->unit ?? 'قطعة']);
                        fputcsv($file, ['الوحدة الكبرى:', $selectedItem->wholesaleUnit?->name ?? '-']);
                        fputcsv($file, ['إجمالي الرصيد المتاح:', $totalBaseStock]);
                        fputcsv($file, ['إجمالي التقييم المالي (ر.س):', number_format($totalValuation, 2)]);
                        fputcsv($file, []);

                        fputcsv($file, ['--- الكميات المتوفرة بالمخازن ---']);
                        fputcsv($file, ['المخزن', 'الفرع', 'الكمية (بالوحدة الأساسية)', 'تفاصيل الكمية', 'التقييم بسعر التكلفة (ر.س)']);
                        foreach ($warehouseStock as $ws) {
                            fputcsv($file, [
                                $ws['warehouse']->name,
                                $ws['warehouse']->branch?->name ?? '-',
                                number_format($ws['qty_in_base'], 2),
                                $ws['formatted_stock'],
                                number_format($ws['valuation'], 2),
                            ]);
                        }
                        fputcsv($file, []);

                        fputcsv($file, ['--- سجل حركات الصنف ---']);
                        fputcsv($file, ['التاريخ والوقت', 'المخزن', 'نوع الحركة', 'الكمية', 'نوع المرجع', 'البيان', 'المستخدم']);
                        foreach ($movements as $m) {
                            $typeText = match ($m->movement_type) {
                                'in' => 'وارد',
                                'out' => 'منصرف',
                                'transfer' => 'تحويل',
                                default => $m->movement_type,
                            };
                            fputcsv($file, [
                                $m->created_at?->format('Y-m-d H:i') ?? '-',
                                $m->warehouse?->name ?? '-',
                                $typeText,
                                number_format($m->quantity, 2),
                                $m->reference_type_name ?? '-',
                                $m->notes ?? '-',
                                $m->creator?->name ?? '-',
                            ]);
                        }

                        fclose($file);
                    };

                    return Response::stream($callback, 200, $headers);
                }

                if ($request->input('export') === 'print' || $request->input('print') === '1') {
                    return view('inventory.item-card-print', compact('selectedItem', 'warehouseStock', 'movements', 'totalBaseStock', 'totalValuation', 'stockStatus', 'warehousesWithStockCount'));
                }
            }
        }

        return view('inventory.item-card', compact(
            'allItems',
            'selectedItem',
            'selectedItemId',
            'warehouses',
            'warehouseStock',
            'movements',
            'totalBaseStock',
            'totalValuation',
            'stockStatus',
            'warehousesWithStockCount'
        ));
    }
}
