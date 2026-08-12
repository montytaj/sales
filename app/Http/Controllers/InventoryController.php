<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\Warehouse;
use App\Models\ItemCategory;
use App\Models\InventoryScrap;
use App\Services\InventoryService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class InventoryController extends Controller
{
    use AuthorizesRequests;

    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    public function index(Request $request)
    {
        $this->authorize('manage-inventory');

        if (!setting('inventory_enabled', true)) {
            return redirect()->route('dashboard')->with('error', 'وحدة المخزون معطلة حالياً في إعدادات النظام.');
        }

        $items = InventoryItem::with(['category', 'stockMovements', 'warehouseItems'])->paginate(15);
        $warehouses = Warehouse::where('is_active', true)->get();
        $scraps = InventoryScrap::with(['item', 'warehouse'])->where('status', 'available')->get();

        return view('inventory.index', compact('items', 'warehouses', 'scraps'));
    }

    public function storeMovement(Request $request)
    {
        $this->authorize('manage-inventory');

        if (!setting('inventory_enabled', true)) {
            return back()->with('error', 'وحدة المخزون معطلة.');
        }

        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'item_id' => ['required', 'exists:inventory_items,id'],
            'movement_type' => ['required', 'in:in,out,transfer,adjustment,reservation,waste'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
            $item = InventoryItem::findOrFail($validated['item_id']);

            $this->inventoryService->recordMovement(
                $warehouse,
                $item,
                $validated['movement_type'],
                (float) $validated['quantity'],
                null,
                null,
                $validated['notes'] ?? null
            );

            return back()->with('success', 'تم تسجيل حركة المخزون بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء تسجيل حركة المخزون: ' . $e->getMessage());
        }
    }

    public function storeScrap(Request $request)
    {
        $this->authorize('manage-inventory');

        $validated = $request->validate([
            'warehouse_id' => ['required', 'exists:warehouses,id'],
            'item_id' => ['required', 'exists:inventory_items,id'],
            'dimensions' => ['required', 'string', 'max:100'],
            'quantity' => ['required', 'numeric', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $warehouse = Warehouse::findOrFail($validated['warehouse_id']);
        $item = InventoryItem::findOrFail($validated['item_id']);

        $this->inventoryService->recordScrap($item, $warehouse, $validated['dimensions'], (float) $validated['quantity'], $validated['notes'] ?? null);

        return back()->with('success', 'تم تسجيل بقايا اللوح / الهالك بنجاح.');
    }
}
