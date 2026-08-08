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
}
