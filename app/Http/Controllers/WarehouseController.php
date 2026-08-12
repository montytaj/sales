<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use App\Models\WarehouseItem;
use App\Models\Branch;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index($locale = 'ar')
    {
        $warehouses = Warehouse::withCount('items')->latest()->get();
        $branches = Branch::all();
        return view('warehouses.index', compact('warehouses', 'branches'));
    }

    public function store($locale, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name',
            'code' => 'nullable|string|max:50|unique:warehouses,code',
            'location' => 'nullable|string|max:255',
            'keeper_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        Warehouse::create([
            'name' => $request->name,
            'code' => $request->code ?: ('WH-' . strtoupper(uniqid())),
            'location' => $request->location,
            'keeper_name' => $request->keeper_name,
            'phone' => $request->phone,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم إضافة المخزن بنجاح');
    }

    public function show($locale, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $warehouseItems = WarehouseItem::with(['item.baseUnit', 'item.wholesaleUnit'])
            ->where('warehouse_id', $warehouse->id)
            ->get();

        return view('warehouses.show', compact('warehouse', 'warehouseItems'));
    }

    public function update($locale, Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:warehouses,name,' . $warehouse->id,
            'code' => 'nullable|string|max:50|unique:warehouses,code,' . $warehouse->id,
            'location' => 'nullable|string|max:255',
            'keeper_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'description' => 'nullable|string',
        ]);

        $warehouse->update([
            'name' => $request->name,
            'code' => $request->code,
            'location' => $request->location,
            'keeper_name' => $request->keeper_name,
            'phone' => $request->phone,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('warehouses.index')->with('success', 'تم تحديث البيانات بنجاح');
    }

    public function destroy($locale, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        if ($warehouse->items()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف المخزن لوجود أصناف مخزنية مسجلة فيه.');
        }

        $warehouse->delete();
        return redirect()->route('warehouses.index')->with('success', 'تم حذف المخزن بنجاح');
    }

    /**
     * Show opening balances entry screen for a specific warehouse.
     */
    public function openingBalances($locale = 'ar', $id)
    {
        $warehouse = Warehouse::findOrFail($id);
        $warehouses = Warehouse::where('is_active', true)->get();
        $items = \App\Models\InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit'])
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $existingStock = WarehouseItem::where('warehouse_id', $warehouse->id)
            ->pluck('qty_in_base_units', 'inventory_item_id')
            ->toArray();

        return view('warehouses.opening_balances', compact('warehouse', 'warehouses', 'items', 'existingStock'));
    }

    /**
     * Store opening balances for items in a specific warehouse.
     */
    public function storeOpeningBalances($locale = 'ar', Request $request, $id)
    {
        $warehouse = Warehouse::findOrFail($id);

        $request->validate([
            'balances' => 'required|array',
            'balances.*.wholesale_qty' => 'nullable|numeric|min:0',
            'balances.*.base_qty' => 'nullable|numeric|min:0',
            'balances.*.cost_price' => 'nullable|numeric|min:0',
            'balances.*.notes' => 'nullable|string|max:255',
        ]);

        \Illuminate\Support\Facades\DB::transaction(function () use ($request, $warehouse) {
            foreach ($request->balances as $itemId => $data) {
                $item = \App\Models\InventoryItem::find($itemId);
                if (!$item) continue;

                $wholesaleQty = max(0, (float)($data['wholesale_qty'] ?? 0));
                $baseQty = max(0, (float)($data['base_qty'] ?? 0));
                $conversionFactor = max(1, (float)($item->conversion_factor ?: 1));

                $totalQtyInBase = ($wholesaleQty * $conversionFactor) + $baseQty;

                // Update cost price if provided
                if (isset($data['cost_price']) && (float)$data['cost_price'] > 0) {
                    $item->update([
                        'cost_price' => (float)$data['cost_price'],
                        'default_purchase_price' => (float)$data['cost_price'],
                    ]);
                }

                $whItem = WarehouseItem::where('warehouse_id', $warehouse->id)
                    ->where('inventory_item_id', $item->id)
                    ->first();

                $oldQty = (float)($whItem?->qty_in_base_units ?? 0);

                if ($whItem) {
                    $whItem->update(['qty_in_base_units' => $totalQtyInBase]);
                } else if ($totalQtyInBase > 0) {
                    WarehouseItem::create([
                        'warehouse_id' => $warehouse->id,
                        'inventory_item_id' => $item->id,
                        'qty_in_base_units' => $totalQtyInBase,
                    ]);
                }

                // Log movement if quantity changed or created
                $diff = $totalQtyInBase - $oldQty;
                if (abs($diff) > 0.0001) {
                    \App\Models\StockMovement::create([
                        'warehouse_id' => $warehouse->id,
                        'item_id' => $item->id,
                        'movement_type' => $diff > 0 ? 'in' : 'out',
                        'quantity' => abs($diff),
                        'reference_type' => 'opening_balance',
                        'notes' => !empty($data['notes']) ? $data['notes'] : "تسوية بضاعة أول المدة للمخزن ({$warehouse->name})",
                        'created_by' => auth()->id() ?? 1,
                    ]);
                }
            }
        });

        return redirect()->route('warehouses.show', ['warehouse' => $warehouse->id])
            ->with('success', "تم حفظ وتسوية بضاعة أول المدة لمخزن ({$warehouse->name}) بنجاح.");
    }
}
