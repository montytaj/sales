<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ChequeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-cheques');

        $query = Cheque::with(['voucher.customer', 'creator']);

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('cheque_number', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('drawer_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        $cheques = $query->latest()->paginate(15)->withQueryString();

        return view('finance.cheques.index', compact('cheques'));
    }

    public function show($locale, Cheque $cheque)
    {
        $this->authorize('view-cheques');

        $cheque->load(['voucher.customer', 'creator']);

        return view('finance.cheques.show', compact('cheque'));
    }

    public function updateStatus(Request $request, $locale, Cheque $cheque)
    {
        $this->authorize('manage-cheques');

        $validated = $request->validate([
            'status' => ['required', 'in:received,under_collection,collected,returned,cancelled,deferred'],
            'notes' => ['nullable', 'string'],
        ]);

        $oldStatus = $cheque->status;
        $cheque->update([
            'status' => $validated['status'],
            'notes' => $validated['notes'] ?? $cheque->notes,
        ]);

        ActivityLog::log(
            'cheque_status_updated',
            $cheque,
            "Updated cheque {$cheque->cheque_number} status from {$oldStatus} to {$cheque->status}"
        );

        return back()->with('success', 'تم تحديث حالة الشيك بنجاح.');
    }
}
