<?php

namespace App\Http\Controllers;

use App\Models\ItemCategory;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ItemCategoryController extends Controller
{
    use AuthorizesRequests;

    public function index($locale = 'ar')
    {
        $this->authorize('manage-inventory');
        $categories = ItemCategory::with('parent')->withCount('items')->latest()->get();
        $parentCategories = ItemCategory::whereNull('parent_id')->get();

        return view('categories.index', compact('categories', 'parentCategories'));
    }

    public function store($locale, Request $request)
    {
        $this->authorize('manage-inventory');
        $request->validate([
            'name' => 'required|string|max:255|unique:item_categories,name',
            'code' => 'nullable|string|max:50|unique:item_categories,code',
            'parent_id' => 'nullable|exists:item_categories,id',
            'description' => 'nullable|string',
        ]);

        ItemCategory::create([
            'name' => $request->name,
            'code' => $request->code ?: ('CAT-' . strtoupper(uniqid())),
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('categories.index')->with('success', 'تم إضافة التصنيف بنجاح');
    }

    public function update($locale, Request $request, $id)
    {
        $this->authorize('manage-inventory');
        $category = ItemCategory::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:item_categories,name,' . $category->id,
            'code' => 'nullable|string|max:50|unique:item_categories,code,' . $category->id,
            'parent_id' => 'nullable|exists:item_categories,id',
            'description' => 'nullable|string',
        ]);

        $category->update([
            'name' => $request->name,
            'code' => $request->code,
            'parent_id' => $request->parent_id,
            'description' => $request->description,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('categories.index')->with('success', 'تم تحديث التصنيف بنجاح');
    }

    public function destroy($locale, $id)
    {
        $this->authorize('manage-inventory');
        $category = ItemCategory::findOrFail($id);

        if ($category->items()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف التصنيف لأنه مرتبط بأصناف مخزنية.');
        }

        $category->delete();
        return redirect()->route('categories.index')->with('success', 'تم حذف التصنيف بنجاح');
    }
}
