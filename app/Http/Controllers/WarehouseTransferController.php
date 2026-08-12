<?php

namespace App\Http\Controllers;

use App\Models\WarehouseTransfer;
use App\Models\WarehouseTransferItem;
use App\Models\Warehouse;
use App\Models\InventoryItem;
use App\Services\InventoryService;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\DB;

class WarehouseTransferController extends Controller
{
    use AuthorizesRequests;

    protected InventoryService $inventoryService;

    public function __construct(InventoryService $inventoryService)
    {
        $this->inventoryService = $inventoryService;
    }

    /**
     * Display a listing of warehouse transfers.
     */
    public function index(Request $request)
    {
        $this->authorize('view-warehouse-transfers');

        $query = WarehouseTransfer::with(['fromWarehouse', 'toWarehouse', 'creator', 'approver', 'items'])
            ->withCount('items');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('transfer_number', 'like', "%{$search}%")
                ->orWhereHas('fromWarehouse', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                })
                ->orWhereHas('toWarehouse', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%");
                });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('from_warehouse_id')) {
            $query->where('from_warehouse_id', $request->input('from_warehouse_id'));
        }

        if ($request->filled('to_warehouse_id')) {
            $query->where('to_warehouse_id', $request->input('to_warehouse_id'));
        }

        $transfers = $query->latest('id')->paginate(15)->withQueryString();
        $warehouses = Warehouse::where('is_active', true)->get();

        return view('warehouse_transfers.index', compact('transfers', 'warehouses'));
    }

    /**
     * Show form to create a new transfer request.
     */
    public function create()
    {
        $this->authorize('create-warehouse-transfers');

        $warehouses = Warehouse::where('is_active', true)->get();
        $items = InventoryItem::with(['warehouseItems'])->get();

        return view('warehouse_transfers.create', compact('warehouses', 'items'));
    }

    /**
     * Store a new transfer request.
     */
    public function store(Request $request)
    {
        $this->authorize('create-warehouse-transfers');

        $validated = $request->validate([
            'from_warehouse_id' => ['required', 'exists:warehouses,id', 'different:to_warehouse_id'],
            'to_warehouse_id' => ['required', 'exists:warehouses,id', 'different:from_warehouse_id'],
            'transfer_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.inventory_item_id' => ['required', 'exists:inventory_items,id'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.notes' => ['nullable', 'string'],
            'auto_approve' => ['nullable', 'boolean'],
        ], [
            'from_warehouse_id.different' => 'يجب أن يكون المخزن المصدر مختلفاً عن المخزن الهدف.',
            'to_warehouse_id.different' => 'يجب أن يكون المخزن الهدف مختلفاً عن المخزن المصدر.',
            'items.required' => 'يجب إضافة صنف واحد على الأقل للتحويل.',
        ]);

        if ($validated['from_warehouse_id'] == $validated['to_warehouse_id']) {
            return back()->withInput()->with('error', 'عفواً، لا يمكن إجراء التحويل المخزني من مخزن إلى المخزن نفسه. يرجى اختيار مخزن آخر كهدف.');
        }

        $fromWarehouse = Warehouse::findOrFail($validated['from_warehouse_id']);

        // Check stock levels before saving
        foreach ($validated['items'] as $itemData) {
            $item = InventoryItem::findOrFail($itemData['inventory_item_id']);
            $currentStock = $this->inventoryService->getStockQuantity($item, $fromWarehouse);
            if ($currentStock < (float)$itemData['quantity']) {
                return back()->withInput()->with('error', "عفواً، رصيد الصنف ({$item->name}) في المخزن المصدر ({$fromWarehouse->name}) هو ({$currentStock}) بينما الكمية المطلوبة هي ({$itemData['quantity']}).");
            }
        }

        $transfer = DB::transaction(function () use ($validated) {
            $transferNumber = 'TR-' . date('Ymd') . '-' . sprintf('%04d', WarehouseTransfer::whereDate('created_at', now())->count() + 1);

            $transfer = WarehouseTransfer::create([
                'transfer_number' => $transferNumber,
                'from_warehouse_id' => $validated['from_warehouse_id'],
                'to_warehouse_id' => $validated['to_warehouse_id'],
                'transfer_date' => $validated['transfer_date'],
                'status' => 'pending',
                'notes' => $validated['notes'] ?? null,
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $itemData) {
                WarehouseTransferItem::create([
                    'warehouse_transfer_id' => $transfer->id,
                    'inventory_item_id' => $itemData['inventory_item_id'],
                    'quantity' => $itemData['quantity'],
                    'notes' => $itemData['notes'] ?? null,
                ]);
            }

            ActivityLog::log(
                'warehouse_transfer_created',
                $transfer,
                "Created warehouse transfer request {$transfer->transfer_number}"
            );

            return $transfer;
        });

        // Auto approve if requested and user has approve permission
        if ($request->boolean('auto_approve') && auth()->user()->can('approve-warehouse-transfers')) {
            try {
                $this->inventoryService->executeTransfer($transfer);
                return redirect()->route('warehouse-transfers.show', $transfer->id)
                    ->with('success', 'تم إنشاء واعتماد طلب التحويل بين المخازن وتخصيص الرصيد بنجاح.');
            } catch (\Exception $e) {
                return redirect()->route('warehouse-transfers.show', $transfer->id)
                    ->with('warning', 'تم إنشاء الطلب بنجاح، ولكن حدث خطأ في الترحيل التلقائي: ' . $e->getMessage());
            }
        }

        return redirect()->route('warehouse-transfers.show', $transfer->id)
            ->with('success', 'تم تسجيل طلب التحويل بين المخازن بنجاح وهو قيد الانتظار للاعتماد.');
    }

    /**
     * Show details of a transfer.
     */
    public function show($locale, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('view-warehouse-transfers');

        $warehouseTransfer->load(['fromWarehouse', 'toWarehouse', 'creator', 'approver', 'items.item']);

        return view('warehouse_transfers.show', [
            'transfer' => $warehouseTransfer,
        ]);
    }

    /**
     * Complete/Approve a pending transfer.
     */
    public function complete($locale, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('approve-warehouse-transfers');

        if ($warehouseTransfer->status !== 'pending') {
            return back()->with('error', 'لا يمكن ترحيل طلب غير قائم في انتظار الاعتماد.');
        }

        try {
            $this->inventoryService->executeTransfer($warehouseTransfer);
            return back()->with('success', 'تم اعتماد وتأكيد التحويل بين المخازن وتحديث أرصدة المخازن بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء ترحيل حركة المخازن: ' . $e->getMessage());
        }
    }

    /**
     * Cancel / Reverse a transfer request.
     */
    public function cancel($locale, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('delete-warehouse-transfers');

        if ($warehouseTransfer->status === 'cancelled') {
            return back()->with('error', 'طلب التحويل المخزني ملغى بالفعل.');
        }

        if ($warehouseTransfer->status === 'completed') {
            try {
                $this->inventoryService->reverseTransfer($warehouseTransfer);
                return back()->with('success', 'تم عكس وإلغاء طلب التحويل المخزني المكتمل، وتحديث أرصدة المخازن وإعادة الكميات بنجاح.');
            } catch (\InvalidArgumentException $e) {
                return back()->with('error', $e->getMessage());
            } catch (\Exception $e) {
                return back()->with('error', 'حدث خطأ أثناء عكس التحويل المخزني: ' . $e->getMessage());
            }
        }

        $warehouseTransfer->update([
            'status' => 'cancelled',
        ]);

        ActivityLog::log(
            'warehouse_transfer_cancelled',
            $warehouseTransfer,
            "Cancelled warehouse transfer request {$warehouseTransfer->transfer_number}"
        );

        return back()->with('success', 'تم إلغاء طلب التحويل المخزني بنجاح.');
    }


    /**
     * Delete a transfer record.
     */
    public function destroy($locale, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('delete-warehouse-transfers');

        if ($warehouseTransfer->status === 'completed') {
            return back()->with('error', 'عفواً، لا يمكن حذف طلب تحويل مخزني مكتمل ومرحل بالأرصدة.');
        }

        $warehouseTransfer->delete();

        return redirect()->route('warehouse-transfers.index')->with('success', 'تم حذف مستند التحويل المخزني بنجاح.');
    }

    /**
     * Printable view of warehouse transfer.
     */
    public function print($locale, WarehouseTransfer $warehouseTransfer)
    {
        $this->authorize('view-warehouse-transfers');

        $warehouseTransfer->load(['fromWarehouse', 'toWarehouse', 'creator', 'approver', 'items.item']);

        return view('warehouse_transfers.print', [
            'transfer' => $warehouseTransfer,
        ]);
    }
}
