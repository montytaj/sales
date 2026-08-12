<?php

namespace App\Http\Controllers;

use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Customer;
use App\Models\Warehouse;
use App\Models\Cashbox;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\StockMovement;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Services\AccountResolver;
use App\Http\Requests\StorePosSaleRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class PosController extends Controller
{
    use AuthorizesRequests;

    public function index($locale = 'ar')
    {
        $this->authorize('view-invoices');
        $categories = ItemCategory::where('is_active', true)->orderBy('name')->get();
        $items = InventoryItem::with(['category', 'baseUnit', 'wholesaleUnit', 'warehouseItems'])
            ->where('is_active', true)
            ->get();

        $customers = Customer::where('is_active', true)->get();
        $warehouses = Warehouse::where('is_active', true)->get();
        $cashboxes = Cashbox::where('is_active', true)->get();

        return view('pos.index', compact('categories', 'items', 'customers', 'warehouses', 'cashboxes'));
    }

    public function store($locale = 'ar', StorePosSaleRequest $request)
    {
        $this->authorize('create-invoices');

        return DB::transaction(function () use ($request) {
            $user = Auth::user();
            $warehouseId = $request->input('warehouse_id');
            $customer = $request->filled('customer_id') ? Customer::find($request->input('customer_id')) : Customer::first();
            if (!$customer) {
                $customer = Customer::create([
                    'code' => 'CUST-CASH',
                    'name' => 'عميل نقدي عابر',
                    'phone' => '0000000000',
                    'category' => 'regular',
                    'is_active' => true,
                ]);
            }

            // Invoice number generator
            $invoiceNumber = 'POS-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // 1. Stock availability validation for all items
            $itemBaseQtyTotals = [];
            foreach ($request->input('items') as $rawItem) {
                $invItem = InventoryItem::findOrFail($rawItem['id']);
                $qty = (float)$rawItem['qty'];
                $unitType = $rawItem['unit_type'];

                $qtyInBase = ($unitType === 'wholesale' && $invItem->conversion_factor > 0)
                    ? ($qty * (float)$invItem->conversion_factor)
                    : $qty;

                $itemBaseQtyTotals[$invItem->id] = ($itemBaseQtyTotals[$invItem->id] ?? 0) + $qtyInBase;
            }

            foreach ($itemBaseQtyTotals as $itemId => $totalRequiredBase) {
                $invItem = InventoryItem::find($itemId);
                $whItem = DB::table('warehouse_items')
                    ->where('warehouse_id', $warehouseId)
                    ->where('inventory_item_id', $itemId)
                    ->lockForUpdate()
                    ->first();


                $availableStock = $whItem ? max(0, (float)$whItem->qty_in_base_units) : 0;

                if ($totalRequiredBase > $availableStock) {
                    $unitName = $invItem->baseUnit?->name ?? 'قطعة';
                    return response()->json([
                        'success' => false,
                        'message' => "عفواً، الكمية المطلوبة من الصنف ({$invItem->name}) بـ {$totalRequiredBase} {$unitName} غير متوفرة. الرصيد المتاح حالياً بالمخزن: {$availableStock} {$unitName}.",
                    ], 422);
                }
            }

            $subtotal = 0;
            $itemsData = [];

            foreach ($request->input('items') as $rawItem) {
                $invItem = InventoryItem::findOrFail($rawItem['id']);
                $qty = (float)$rawItem['qty'];
                $price = (float)$rawItem['price'];
                $unitType = $rawItem['unit_type'];

                $lineTotal = $qty * $price;
                $subtotal += $lineTotal;

                // Base qty calculation
                $qtyInBase = ($unitType === 'wholesale' && $invItem->conversion_factor > 0)
                    ? ($qty * (float)$invItem->conversion_factor)
                    : $qty;

                $itemsData[] = [
                    'item' => $invItem,
                    'qty' => $qty,
                    'unit_type' => $unitType,
                    'price' => $price,
                    'line_total' => $lineTotal,
                    'qty_in_base' => $qtyInBase,
                ];
            }

            $discount = (float)$request->input('discount_amount', 0);
            $taxPercent = (float)setting('tax_percentage', 15.00);
            $taxableSubtotal = max(0, $subtotal - $discount);
            $taxAmount = round($taxableSubtotal * ($taxPercent / 100), 2);
            $totalAmount = $taxableSubtotal + $taxAmount;

            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'customer_id' => $customer?->id,
                'warehouse_id' => $warehouseId,
                'branch_id' => $user->main_branch_id ?? 1,
                'created_by' => $user->id,
                'issue_date' => Carbon::now(),
                'due_date' => Carbon::now(),
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'status' => 'paid',
                'notes' => 'فاتورة مبيعات سريعة عبر شاشة الكاشير (POS)',
            ]);

            foreach ($itemsData as $data) {
                $item = $data['item'];
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'inventory_item_id' => $item->id,
                    'item_name' => $item->name,
                    'description' => 'بيع كاشير - ' . ($data['unit_type'] == 'wholesale' ? 'جملة' : 'قطاعي'),
                    'quantity' => $data['qty'],
                    'unit_of_measure' => $data['unit_type'] == 'wholesale' ? ($item->wholesaleUnit?->name ?? 'كرتونة') : ($item->baseUnit?->name ?? 'قطعة'),
                    'unit_price' => $data['price'],
                    'subtotal' => $data['line_total'],
                    'discount_amount' => 0,
                    'tax_percent' => $taxPercent,
                    'tax_amount' => round($data['line_total'] * ($taxPercent / 100), 2),
                    'total' => $data['line_total'] * (1 + ($taxPercent / 100)),
                ]);

                // Stock Movement
                StockMovement::create([
                    'item_id' => $item->id,
                    'warehouse_id' => $warehouseId,
                    'movement_type' => 'out',
                    'quantity' => $data['qty_in_base'],
                    'reference_type' => 'Invoice',
                    'reference_id' => $invoice->id,
                    'notes' => "خصم مخزون مبيعات كاشير ف: {$invoiceNumber}",
                    'created_by' => $user->id,
                ]);

                // Deduct warehouse item stock safely with row lock
                $whItem = DB::table('warehouse_items')
                    ->where('warehouse_id', $warehouseId)
                    ->where('inventory_item_id', $item->id)
                    ->lockForUpdate()
                    ->first();


                if ($whItem) {
                    $newQty = max(0, (float)$whItem->qty_in_base_units - $data['qty_in_base']);
                    DB::table('warehouse_items')
                        ->where('id', $whItem->id)
                        ->update(['qty_in_base_units' => $newQty, 'updated_at' => now()]);
                } else {
                    DB::table('warehouse_items')->insert([
                        'warehouse_id' => $warehouseId,
                        'inventory_item_id' => $item->id,
                        'qty_in_base_units' => 0,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // Journal Entry
            $cashboxModel = !empty($cashboxId) ? Cashbox::find($cashboxId) : null;
            $cashAccount = AccountResolver::getCashboxAccount($cashboxModel);
            $salesAccount = AccountResolver::getSalesAccount();
            $vatAccount = AccountResolver::getVatAccount();


            if ($cashAccount && $salesAccount) {
                $je = JournalEntry::create([
                    'entry_number' => 'JE-POS-' . $invoice->id,
                    'entry_date' => Carbon::now(),
                    'description' => "مبيعات نقدية كاشير فاتورة رقم {$invoiceNumber}",
                    'created_by' => $user->id,
                    'branch_id' => $user->main_branch_id ?? 1,
                    'status' => 'posted',
                ]);

                // Debit Cash
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $cashAccount->id,
                    'debit' => $totalAmount,
                    'credit' => 0,
                    'memo' => "تحصيل مبيعات POS",
                ]);

                // Credit Sales Revenue
                JournalEntryLine::create([
                    'journal_entry_id' => $je->id,
                    'account_id' => $salesAccount->id,
                    'debit' => 0,
                    'credit' => $taxableSubtotal,
                    'memo' => "إيراد مبيعات POS",
                ]);

                // Credit VAT Output Tax if applicable
                if ($taxAmount > 0 && $vatAccount) {
                    JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $vatAccount->id,
                        'debit' => 0,
                        'credit' => $taxAmount,
                        'memo' => "ضريبة قيمة مضافة مبيعات POS ({$taxPercent}%)",
                    ]);
                }

            }

            return response()->json([
                'success' => true,
                'message' => 'تم حفظ الفاتورة وطباعة الإيصال بنجاح',
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'redirect' => route('invoices.show', $invoice->id),
            ]);
        });
    }
}
