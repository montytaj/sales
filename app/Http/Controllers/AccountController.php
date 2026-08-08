<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function index($locale = 'ar')
    {
        // Fetch Level 1 root accounts with recursive children up to level 5
        $accounts = Account::whereNull('parent_id')
            ->with(['children.children.children.children'])
            ->orderBy('code')
            ->get();

        $allAccounts = Account::orderBy('code')->get();

        return view('accounting.tree', compact('accounts', 'allAccounts'));
    }

    public function store($locale, Request $request = null)
    {
        $request = $request ?? request();

        $request->validate([
            'code' => 'required|string|max:50|unique:accounts,code',
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:accounts,id',
            'type' => 'required|in:asset,liability,equity,revenue,expense',
            'nature' => 'required|in:debit,credit',
            'level' => 'required|integer|min:1|max:5',
        ]);

        $parent = $request->parent_id ? Account::find($request->parent_id) : null;
        $level = $parent ? ($parent->level + 1) : 1;
        if ($level > 5) {
            return redirect()->back()->with('error', 'لا يمكن إضافة حساب بمستوى يتجاوز المستوى الخامس (5).');
        }

        // Level 5 accounts are selectable for transactions, higher levels are group headers
        $isSelectable = ($level == 5);

        Account::create([
            'code' => $request->code,
            'name' => $request->name,
            'parent_id' => $request->parent_id,
            'level' => $level,
            'type' => $request->type,
            'nature' => $request->nature,
            'is_selectable' => $isSelectable,
            'is_active' => true,
        ]);

        return redirect()->route('accounting.index')->with('success', 'تم إضافة الحساب لشجرة الحسابات بنجاح');
    }

    public function update($locale, Request $request = null, $id = null)
    {
        $account = ($id instanceof Account) ? $id : Account::findOrFail($id);
        $request = $request ?? request();

        $request->validate([
            'name' => 'required|string|max:255',
            'nature' => 'required|in:debit,credit',
        ]);

        $account->update([
            'name' => $request->name,
            'nature' => $request->nature,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('accounting.index')->with('success', 'تم تحديث بيانات الحساب بنجاح');
    }

    public function destroy($locale = 'ar', $id = null)
    {
        $account = ($id instanceof Account) ? $id : Account::findOrFail($id);

        if ($account->children()->count() > 0 || $account->journalLines()->count() > 0) {
            return redirect()->route('accounting.index')->with('error', 'لا يمكن حذف هذا الحساب لأنه يحتوي على حسابات فرعية أو قيود يومية.');
        }

        $account->delete();
        return redirect()->route('accounting.index')->with('success', 'تم حذف الحساب بنجاح');
    }
}
