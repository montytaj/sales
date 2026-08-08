<?php

namespace App\Http\Controllers;

use App\Models\Cashbox;
use App\Models\CashboxShift;
use App\Models\Branch;
use App\Models\User;
use App\Models\ActivityLog;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CashboxController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-cashboxes');

        $query = Cashbox::with(['branch', 'shifts' => function ($q) {
            $q->where('status', 'open');
        }]);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_ar', 'like', "%{$search}%")
                  ->orWhere('name_en', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $cashboxes = $query->latest()->paginate(15)->withQueryString();

        return view('finance.cashboxes.index', compact('cashboxes'));
    }

    public function create()
    {
        $this->authorize('create-cashboxes');

        $branches = Branch::where('is_active', true)->get();
        $users = User::where('is_active', true)->get();

        return view('finance.cashboxes.create', compact('branches', 'users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create-cashboxes');

        $validated = $request->validate([
            'name_ar' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'branch_id' => ['nullable', 'exists:branches,id'],
            'opening_balance' => ['required', 'numeric', 'min:0'],
            'users' => ['nullable', 'array'],
            'users.*' => ['exists:users,id'],
        ]);

        $cashbox = Cashbox::create([
            'code' => Cashbox::generateCode(),
            'name_ar' => $validated['name_ar'],
            'name_en' => $validated['name_en'] ?? null,
            'branch_id' => $validated['branch_id'] ?? null,
            'opening_balance' => $validated['opening_balance'],
            'current_balance' => $validated['opening_balance'],
            'is_active' => true,
        ]);

        if (!empty($validated['users'])) {
            $cashbox->users()->sync($validated['users']);
        }

        ActivityLog::log(
            'cashbox_created',
            $cashbox,
            "Created cashbox {$cashbox->name_ar} with opening balance SAR {$cashbox->opening_balance}"
        );

        return redirect()->route('cashboxes.index')->with('success', 'تم إنشاء الخزنة بنجاح.');
    }

    public function show($locale, Cashbox $cashbox)
    {
        $this->authorize('view-cashboxes');

        $cashbox->load(['branch', 'users', 'shifts.user']);
        $activeShift = $cashbox->activeShift();
        $allCashboxes = Cashbox::where('is_active', true)->where('id', '!=', $cashbox->id)->get();

        return view('finance.cashboxes.show', compact('cashbox', 'activeShift', 'allCashboxes'));
    }

    public function openShift(Request $request, $locale, Cashbox $cashbox)
    {
        $this->authorize('manage-cashbox-shifts');

        if ($cashbox->activeShift()) {
            return back()->with('error', 'يوجد وردية مفتوحة بالفعل لهذه الخزنة.');
        }

        CashboxShift::create([
            'cashbox_id' => $cashbox->id,
            'user_id' => auth()->id(),
            'opened_at' => now(),
            'opening_balance' => $cashbox->current_balance,
            'expected_closing_balance' => $cashbox->current_balance,
            'status' => 'open',
            'notes' => $request->input('notes'),
        ]);

        ActivityLog::log(
            'cashbox_shift_opened',
            $cashbox,
            "Opened shift for cashbox {$cashbox->name_ar}"
        );

        return back()->with('success', 'تم فتح الوردية والجرد بنجاح.');
    }

    public function closeShift(Request $request, $locale, Cashbox $cashbox)
    {
        $this->authorize('manage-cashbox-shifts');

        $shift = $cashbox->activeShift();
        if (!$shift) {
            return back()->with('error', 'لا يوجد وردية مفتوحة لإغلاقها.');
        }

        $validated = $request->validate([
            'actual_closing_balance' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $actual = (float) $validated['actual_closing_balance'];
        $expected = (float) $cashbox->current_balance;
        $diff = round($actual - $expected, 2);

        $shift->update([
            'closed_at' => now(),
            'expected_closing_balance' => $expected,
            'actual_closing_balance' => $actual,
            'difference_amount' => $diff,
            'status' => 'closed',
            'notes' => $validated['notes'] ?? null,
        ]);

        ActivityLog::log(
            'cashbox_shift_closed',
            $cashbox,
            "Closed shift for cashbox {$cashbox->name_ar}. Expected: SAR {$expected}, Actual: SAR {$actual}, Difference: SAR {$diff}"
        );

        return back()->with('success', 'تم إغلاق الوردية وحفظ نتائج الجرد بنجاح.');
    }

    public function transfer(Request $request, $locale, Cashbox $cashbox, PaymentService $paymentService)
    {
        $this->authorize('create-payment-vouchers');

        $validated = $request->validate([
            'target_cashbox_id' => ['required', 'exists:cashboxes,id', 'different:cashbox_id'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            $voucherData = [
                'type' => 'transfer',
                'cashbox_id' => $cashbox->id,
                'target_cashbox_id' => $validated['target_cashbox_id'],
                'amount' => $validated['amount'],
                'payment_date' => now()->format('Y-m-d'),
                'notes' => $validated['notes'] ?? 'تحويل بين الخزن',
            ];

            $paymentLines = [
                [
                    'payment_method' => 'cash',
                    'amount' => $validated['amount'],
                    'notes' => 'تحويل بين الخزن',
                ]
            ];

            $paymentService->createVoucher($voucherData, $paymentLines);

            return back()->with('success', 'تم التحويل بين الخزن بنجاح.');
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}
