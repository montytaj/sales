<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
    public function index($locale = 'ar')
    {
        $units = Unit::withCount(['baseItems', 'wholesaleItems'])->latest()->get();
        return view('units.index', compact('units'));
    }

    public function store($locale, Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
            'symbol' => 'nullable|string|max:20',
        ]);

        Unit::create([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('units.index')->with('success', 'تم إضافة وحدة القياس بنجاح');
    }

    public function update($locale, Request $request, $id)
    {
        $unit = Unit::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:units,name,' . $unit->id,
            'symbol' => 'nullable|string|max:20',
        ]);

        $unit->update([
            'name' => $request->name,
            'symbol' => $request->symbol,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('units.index')->with('success', 'تم تحديث وحدة القياس بنجاح');
    }

    public function destroy($locale, $id)
    {
        $unit = Unit::findOrFail($id);

        if ($unit->baseItems()->count() > 0 || $unit->wholesaleItems()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف وحدة القياس لأنها مرتبطة بأصناف مخزنية.');
        }

        $unit->delete();
        return redirect()->route('units.index')->with('success', 'تم حذف وحدة القياس بنجاح');
    }
}
