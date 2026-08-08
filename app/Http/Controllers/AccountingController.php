<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CostCenter;
use App\Models\FiscalPeriod;
use App\Models\JournalEntry;
use App\Services\AccountingService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class AccountingController extends Controller
{
    use AuthorizesRequests;

    protected AccountingService $accountingService;

    public function __construct(AccountingService $accountingService)
    {
        $this->accountingService = $accountingService;
    }

    public function index()
    {
        $this->authorize('view-accounting');

        $accounts = Account::orderBy('code')->get();
        $rootAccounts = Account::whereNull('parent_id')->with('children.children')->orderBy('code')->get();
        $journalEntries = JournalEntry::with(['lines.account', 'poster'])->latest()->paginate(15);
        $costCenters = CostCenter::all();
        $fiscalPeriods = FiscalPeriod::all();

        $accountStats = [
            'assets' => Account::where('type', 'asset')->count(),
            'liabilities' => Account::where('type', 'liability')->count(),
            'equity' => Account::where('type', 'equity')->count(),
            'revenue' => Account::where('type', 'revenue')->count(),
            'expense' => Account::where('type', 'expense')->count(),
        ];

        return view('accounting.index', compact('accounts', 'rootAccounts', 'journalEntries', 'costCenters', 'fiscalPeriods', 'accountStats'));
    }


    public function storeAccount(Request $request)
    {
        $this->authorize('manage-accounting');

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,revenue,expense'],
            'parent_id' => ['nullable', 'exists:chart_of_accounts,id'],
        ]);

        Account::create([
            'code' => $validated['code'],
            'name' => $validated['name'],
            'type' => $validated['type'],
            'parent_id' => $validated['parent_id'] ?? null,
            'is_active' => true,
        ]);

        return back()->with('success', 'تم إضافة الحساب لدليل الحسابات بنجاح.');
    }

    public function storeJournalEntry(Request $request)
    {
        $this->authorize('manage-accounting');

        $validated = $request->validate([
            'entry_date' => ['required', 'date'],
            'description' => ['required', 'string'],
            'lines' => ['required', 'array', 'min:2'],
            'lines.*.account_id' => ['required', 'exists:chart_of_accounts,id'],
            'lines.*.debit' => ['required', 'numeric', 'min:0'],
            'lines.*.credit' => ['required', 'numeric', 'min:0'],
            'lines.*.description' => ['nullable', 'string'],
        ]);

        try {
            $entry = $this->accountingService->createJournalEntry(
                $validated['entry_date'],
                $validated['description'],
                $validated['lines'],
                null,
                null,
                false
            );

            return back()->with('success', "تم إضافة القيد المحاسبي برقم ({$entry->entry_number}) وبانتظار الترحيل.");
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء إضافة القيد: ' . $e->getMessage());
        }
    }

    public function postEntry($locale, JournalEntry $journalEntry)
    {
        $this->authorize('manage-accounting');

        try {
            $this->accountingService->postJournalEntry($journalEntry);
            return back()->with('success', 'تم ترحيل القيد المحاسبي وحظره من التعديل بنجاح.');
        } catch (\InvalidArgumentException $e) {
            return back()->with('error', $e->getMessage());
        } catch (\Exception $e) {
            return back()->with('error', 'حدث خطأ أثناء الترحيل: ' . $e->getMessage());
        }
    }
}
