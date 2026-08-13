<?php

namespace App\Http\Controllers;

use App\Models\Currency;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CurrencyController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('manage-settings');
        $currencies = Currency::orderBy('is_base', 'desc')->orderBy('name', 'asc')->get();
        $baseCurrency = Currency::getBaseCurrency();

        return view('settings.currencies.index', compact('currencies', 'baseCurrency'));
    }

    public function store(Request $request)
    {
        $this->authorize('manage-settings');
        $validated = $request->validate([
            'code' => 'required|string|max:10|unique:currencies,code',
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:15',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['code'] = strtoupper($validated['code']);
        $validated['is_active'] = $request->has('is_active');
        $validated['is_base'] = false;

        Currency::create($validated);
        Cache::forget('active_currencies_list');

        return redirect()->back()->with('success', app()->getLocale() == 'ar' ? 'تمت إضافة العملة الجديدة بنجاح.' : 'Currency added successfully.');
    }

    public function update(Request $request, $locale, $currency = null)
    {
        $this->authorize('manage-settings');
        $currencyModel = $currency instanceof Currency ? $currency : ($locale instanceof Currency ? $locale : Currency::findOrFail($currency ?? $locale));

        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'symbol' => 'nullable|string|max:15',
            'exchange_rate' => 'required|numeric|min:0.000001',
            'is_active' => 'nullable|boolean',
        ]);

        if ($currencyModel->is_base) {
            $validated['exchange_rate'] = 1.000000;
            $validated['is_active'] = true;
        } else {
            $validated['is_active'] = $request->has('is_active');
        }

        $currencyModel->update($validated);
        Cache::forget('active_currencies_list');

        return redirect()->back()->with('success', app()->getLocale() == 'ar' ? 'تم تحديث بيانات وسعر صرف العملة بنجاح.' : 'Currency updated successfully.');
    }

    public function setBase($locale, $currency = null)
    {
        $this->authorize('manage-settings');
        $currencyModel = $currency instanceof Currency ? $currency : ($locale instanceof Currency ? $locale : Currency::findOrFail($currency ?? $locale));

        Currency::where('is_base', true)->update(['is_base' => false]);
        $currencyModel->update(['is_base' => true, 'exchange_rate' => 1.000000, 'is_active' => true]);
        
        setting(['currency' => $currencyModel->code]);
        Cache::forget('active_currencies_list');

        return redirect()->back()->with('success', app()->getLocale() == 'ar' ? "تم تعيين ({$currencyModel->name}) كعملة أساسية للنظام بنجاح." : "Set {$currencyModel->name} as base currency.");
    }

    public function destroy($locale, $currency = null)
    {
        $this->authorize('manage-settings');
        $currencyModel = $currency instanceof Currency ? $currency : ($locale instanceof Currency ? $locale : Currency::findOrFail($currency ?? $locale));

        if ($currencyModel->is_base) {
            return redirect()->back()->with('error', app()->getLocale() == 'ar' ? 'لا يمكن حذف العملة الأساسية للنظام.' : 'Cannot delete base currency.');
        }

        $currencyModel->delete();
        Cache::forget('active_currencies_list');

        return redirect()->back()->with('success', app()->getLocale() == 'ar' ? 'تم حذف العملة بنجاح.' : 'Currency deleted successfully.');
    }
}
