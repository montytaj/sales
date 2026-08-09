<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseItem;
use Illuminate\Http\Request;

class InventoryItemController extends Controller
{
    public function index($locale = 'ar')
    {
        if (!setting('inventory_enabled', true)) {
            return redirect()->route('dashboard', ['locale' => $locale])->with('error', 'وحدة المخزون معطلة حالياً في إعدادات النظام.');
        }

        $items = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems.warehouse'])
            ->latest()
            ->get();

        return view('inventory.index', compact('items'));
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

        // Initial stock insertion per warehouse if specified
        if ($request->has('initial_stock') && is_array($request->initial_stock)) {
            foreach ($request->initial_stock as $warehouseId => $qtyInWholesale) {
                if ($qtyInWholesale > 0) {
                    $qtyInBase = $qtyInWholesale * $request->conversion_factor;
                    WarehouseItem::updateOrCreate(
                        ['warehouse_id' => $warehouseId, 'inventory_item_id' => $item->id],
                        ['qty_in_base_units' => $qtyInBase]
                    );
                }
            }
        }

        return redirect()->route('inventory.index')->with('success', 'تم إضافة الصنف بنجاح وإعداد تفاصيل كروت المخزن والوحدات المزدوجة.');
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
}
