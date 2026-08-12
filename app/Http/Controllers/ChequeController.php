<?php

namespace App\Http\Controllers;

use App\Models\Cheque;
use App\Models\Cashbox;
use App\Models\Account;
use App\Models\Invoice;
use App\Models\PurchaseInvoice;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\ActivityLog;
use App\Services\AccountResolver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;



class ChequeController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $this->authorize('view-cheques');

        $query = Cheque::with(['voucher.customer', 'voucher.supplier', 'cashbox', 'account', 'creator']);

        // Type filter (incoming / outgoing)
        if ($request->filled('type')) {
            $query->where('type', $request->input('type'));
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // Tab filter helper
        if ($request->filled('tab')) {
            $tab = $request->input('tab');
            if ($tab === 'incoming') {
                $query->incoming();
            } elseif ($tab === 'outgoing') {
                $query->outgoing();
            } elseif ($tab === 'pending') {
                $query->pending();
            } elseif ($tab === 'cleared') {
                $query->cleared();
            } elseif ($tab === 'bounced') {
                $query->bounced();
            }
        }

        // Search query
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('cheque_number', 'like', "%{$search}%")
                  ->orWhere('bank_name', 'like', "%{$search}%")
                  ->orWhere('drawer_name', 'like', "%{$search}%")
                  ->orWhere('payee_name', 'like', "%{$search}%");
            });
        }

        // Date filters
        if ($request->filled('due_from')) {
            $query->whereDate('due_date', '>=', $request->input('due_from'));
        }
        if ($request->filled('due_to')) {
            $query->whereDate('due_date', '<=', $request->input('due_to'));
        }

        $cheques = $query->latest()->paginate(15)->withQueryString();

        // Calculate Counters & Summaries by Type & Status
        $counts = [
            'all' => Cheque::count(),
            'all_amount' => (float) Cheque::sum('amount'),
            'incoming' => Cheque::incoming()->count(),
            'incoming_amount' => (float) Cheque::incoming()->sum('amount'),
            'outgoing' => Cheque::outgoing()->count(),
            'outgoing_amount' => (float) Cheque::outgoing()->sum('amount'),
            'pending' => Cheque::pending()->count(),
            'pending_amount' => (float) Cheque::pending()->sum('amount'),
            'cleared' => Cheque::cleared()->count(),
            'cleared_amount' => (float) Cheque::cleared()->sum('amount'),
            'bounced' => Cheque::bounced()->count(),
            'bounced_amount' => (float) Cheque::bounced()->sum('amount'),
        ];

        $cashboxes = Cashbox::where('is_active', true)->get();
        $accounts = Account::where('is_active', true)->get();

        return view('finance.cheques.index', compact('cheques', 'counts', 'cashboxes', 'accounts'));
    }

    public function show($locale, Cheque $cheque)
    {
        $this->authorize('view-cheques');

        $cheque->load(['voucher.customer', 'voucher.supplier', 'cashbox', 'account', 'journalEntry.lines.account', 'creator']);

        return view('finance.cheques.show', compact('cheque'));
    }

    /**
     * Clear / Collect Cheque (تحصيل الشيك وإيداعه بالبنك/الخزينة)
     */
    public function clear(Request $request, $locale, Cheque $cheque)
    {
        $this->authorize('manage-cheques');

        $validated = $request->validate([
            'cashbox_id' => ['required', 'exists:cashboxes,id'],
            'clear_date' => ['required', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($cheque->status === 'collected') {
            return back()->with('error', 'هذا الشيك محصل بالفعل.');
        }

        DB::transaction(function () use ($cheque, $validated) {
            $cashbox = Cashbox::lockForUpdate()->findOrFail($validated['cashbox_id']);
            $clearDate = $validated['clear_date'];

            // 1. Update Cheque Status & Clearing Data
            $cheque->update([
                'status' => 'collected',
                'cashbox_id' => $cashbox->id,
                'cleared_at' => $clearDate,
                'notes' => $validated['notes'] ?? $cheque->notes,
            ]);

            // 2. Adjust Cashbox Balance
            if ($cheque->type === 'outgoing') {
                $cashbox->decrement('current_balance', $cheque->amount);
            } else {
                $cashbox->increment('current_balance', $cheque->amount);
            }

            // 3. Create Accounting Journal Entry
            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $clearDate,
                'reference_type' => Cheque::class,
                'reference_id' => $cheque->id,
                'description' => ($cheque->type === 'outgoing' ? 'سداد شيك صادر' : 'تحصيل شيك وارد') . " رقم {$cheque->cheque_number} - خزينة/بنك: {$cashbox->name}",
                'status' => 'posted',
                'posted_by' => auth()->id(),
                'posted_at' => now(),
            ]);

            // Resolve Cashbox Account and Cheque Account dynamically
            $cashboxAccount = AccountResolver::getCashboxAccount($cashbox);
            $chequeAccount = AccountResolver::getChequesUnderCollectionAccount();


            if ($cashboxAccount && $chequeAccount) {
                if ($cheque->type === 'outgoing') {
                    // Outgoing: Debit Cheques Payable, Credit Cashbox
                    $entry->lines()->create([
                        'account_id' => $chequeAccount->id,
                        'debit' => $cheque->amount,
                        'credit' => 0.00,
                        'description' => "سداد شيك صادر رقم {$cheque->cheque_number}",
                    ]);
                    $entry->lines()->create([
                        'account_id' => $cashboxAccount->id,
                        'debit' => 0.00,
                        'credit' => $cheque->amount,
                        'description' => "صرف من الخزينة/البنك {$cashbox->name}",
                    ]);
                } else {
                    // Incoming: Debit Cashbox, Credit Cheques under collection
                    $entry->lines()->create([
                        'account_id' => $cashboxAccount->id,
                        'debit' => $cheque->amount,
                        'credit' => 0.00,
                        'description' => "إيداع شيك تحصيل بالخزينة/البنك {$cashbox->name}",
                    ]);
                    $entry->lines()->create([
                        'account_id' => $chequeAccount->id,
                        'debit' => 0.00,
                        'credit' => $cheque->amount,
                        'description' => "تحصيل شيك وارد رقم {$cheque->cheque_number}",
                    ]);
                }
            }

            $cheque->update(['journal_entry_id' => $entry->id]);

            // Sync connected invoice / purchase invoice status upon cheque collection
            $voucher = $cheque->voucher;
            if ($voucher) {
                $paymentService = app(\App\Services\PaymentService::class);
                if ($voucher->invoice) {
                    $paymentService->syncInvoicePaymentStatus($voucher->invoice);
                }
                if ($voucher->purchaseInvoice) {
                    $paymentService->syncPurchaseInvoicePaymentStatus($voucher->purchaseInvoice);
                }
            }

            ActivityLog::log(
                'cheque_cleared',
                $cheque,
                "Cleared cheque {$cheque->cheque_number} of amount SAR {$cheque->amount} to cashbox {$cashbox->name}"
            );

        });

        return back()->with('success', 'تم تحصيل الشيك وإيداعه في الحساب بنجاح وإصدار القيد المحاسبي.');
    }

    /**
     * Bounce Cheque (إرجاع / ارتداد الشيك)
     */
    public function bounce(Request $request, $locale, Cheque $cheque)
    {
        $this->authorize('manage-cheques');

        $validated = $request->validate([
            'notes' => ['required', 'string'],
        ]);

        DB::transaction(function () use ($cheque, $validated) {
            $oldStatus = $cheque->status;
            $cheque->load(['voucher.customer', 'voucher.supplier', 'cashbox']);

            // 1. Revert Cashbox Balance if previously collected
            if ($oldStatus === 'collected' && $cheque->cashbox_id) {
                $cashbox = Cashbox::lockForUpdate()->find($cheque->cashbox_id);
                if ($cashbox) {
                    if ($cheque->type === 'outgoing') {
                        $cashbox->increment('current_balance', $cheque->amount);
                    } else {
                        $cashbox->decrement('current_balance', $cheque->amount);
                    }
                }
            }

            // 2. Issue Bounced Cheque Reversing Journal Entry
            $existingBounceJe = JournalEntry::where('entry_number', 'JE-BOUNCE-CHQ-' . $cheque->id)->first();
            if (!$existingBounceJe) {
                $voucher = $cheque->voucher;
                $cashbox = $cheque->cashbox;

                $bounceJe = JournalEntry::create([
                    'entry_number' => 'JE-BOUNCE-CHQ-' . $cheque->id,
                    'entry_date' => now()->toDateString(),
                    'reference_type' => Cheque::class,
                    'reference_id' => $cheque->id,
                    'description' => "قيد عكسي لشيك مرتد رقم {$cheque->cheque_number} - السبب: {$validated['notes']}",
                    'status' => 'posted',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                if ($cheque->type === 'incoming') {
                    // Incoming Cheque: Debit Customer AR (re-establish debt), Credit Cashbox/Bank or Cheque Account
                    $customerAccount = AccountResolver::getCustomerAccount($voucher?->customer);
                    $creditAccount = ($oldStatus === 'collected')
                        ? AccountResolver::getCashboxAccount($cashbox)
                        : AccountResolver::getChequesUnderCollectionAccount();

                    if ($customerAccount && $creditAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $bounceJe->id,
                            'account_id' => $customerAccount->id,
                            'debit' => $cheque->amount,
                            'credit' => 0.00,
                            'description' => "إعادة الدين للعميل نتيجة ارتداد الشيك رقم {$cheque->cheque_number}",
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $bounceJe->id,
                            'account_id' => $creditAccount->id,
                            'debit' => 0.00,
                            'credit' => $cheque->amount,
                            'description' => "عكس تحصيل الخزينة/الحساب للشيك المرتد رقم {$cheque->cheque_number}",
                        ]);
                    }
                } else {
                    // Outgoing Cheque: Debit Cashbox/Bank or Cheque Account, Credit Supplier AP (re-establish liability)
                    $supplierAccount = AccountResolver::getSupplierAccount($voucher?->supplier);
                    $debitAccount = ($oldStatus === 'collected')
                        ? AccountResolver::getCashboxAccount($cashbox)
                        : AccountResolver::getChequesUnderCollectionAccount();

                    if ($supplierAccount && $debitAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $bounceJe->id,
                            'account_id' => $debitAccount->id,
                            'debit' => $cheque->amount,
                            'credit' => 0.00,
                            'description' => "عكس خصم الخزينة/الحساب للشيك الصادر المرتد رقم {$cheque->cheque_number}",
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $bounceJe->id,
                            'account_id' => $supplierAccount->id,
                            'debit' => 0.00,
                            'credit' => $cheque->amount,
                            'description' => "إعادة المستحق للمورد نتيجة ارتداد الشيك الصادر رقم {$cheque->cheque_number}",
                        ]);
                    }
                }
            }

            // 3. Re-establish Invoice Due Amount & Status if linked
            $voucher = $cheque->voucher;
            if ($voucher) {
                $paymentService = app(\App\Services\PaymentService::class);
                if ($voucher->invoice) {
                    $paymentService->syncInvoicePaymentStatus($voucher->invoice);
                }
                if ($voucher->purchaseInvoice) {
                    $paymentService->syncPurchaseInvoicePaymentStatus($voucher->purchaseInvoice);
                }
            }


            $cheque->update([
                'status' => 'returned',
                'notes' => $validated['notes'],
            ]);

            ActivityLog::log(
                'cheque_bounced',
                $cheque,
                "Bounced cheque {$cheque->cheque_number} with reason: {$validated['notes']}"
            );
        });

        return back()->with('warning', 'تم إرجاع الشيك وتسجيل القيد المحاسبي العكسي وإعادة الدين بنجاح.');
    }

    /**
     * Cancel Cheque (إلغاء الشيك)
     */
    public function cancel(Request $request, $locale, Cheque $cheque)
    {
        $this->authorize('manage-cheques');

        $validated = $request->validate([
            'notes' => ['nullable', 'string'],
        ]);

        DB::transaction(function () use ($cheque, $validated) {
            $oldStatus = $cheque->status;
            $notes = $validated['notes'] ?? $cheque->notes ?? 'إلغاء الشيك';
            $cheque->load(['voucher.customer', 'voucher.supplier', 'cashbox']);

            if ($oldStatus === 'collected' && $cheque->cashbox_id) {
                $cashbox = Cashbox::lockForUpdate()->find($cheque->cashbox_id);
                if ($cashbox) {
                    if ($cheque->type === 'outgoing') {
                        $cashbox->increment('current_balance', $cheque->amount);
                    } else {
                        $cashbox->decrement('current_balance', $cheque->amount);
                    }
                }
            }

            // Issue Reversing Journal Entry if cheque was processed
            $existingCancelJe = JournalEntry::where('entry_number', 'JE-CANCEL-CHQ-' . $cheque->id)->first();
            if (!$existingCancelJe) {
                $voucher = $cheque->voucher;
                $cashbox = $cheque->cashbox;

                $cancelJe = JournalEntry::create([
                    'entry_number' => 'JE-CANCEL-CHQ-' . $cheque->id,
                    'entry_date' => now()->toDateString(),
                    'reference_type' => Cheque::class,
                    'reference_id' => $cheque->id,
                    'description' => "قيد عكسي لشك ملغى رقم {$cheque->cheque_number} - السبب: {$notes}",
                    'status' => 'posted',
                    'posted_by' => auth()->id(),
                    'posted_at' => now(),
                ]);

                if ($cheque->type === 'incoming') {
                    $customerAccount = AccountResolver::getCustomerAccount($voucher?->customer);
                    $creditAccount = ($oldStatus === 'collected')
                        ? AccountResolver::getCashboxAccount($cashbox)
                        : AccountResolver::getChequesUnderCollectionAccount();

                    if ($customerAccount && $creditAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $cancelJe->id,
                            'account_id' => $customerAccount->id,
                            'debit' => $cheque->amount,
                            'credit' => 0.00,
                            'description' => "إعادة الدين للعميل نتيجة إلغاء الشيك رقم {$cheque->cheque_number}",
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $cancelJe->id,
                            'account_id' => $creditAccount->id,
                            'debit' => 0.00,
                            'credit' => $cheque->amount,
                            'description' => "عكس حساب الشيك الملغى رقم {$cheque->cheque_number}",
                        ]);
                    }
                } else {
                    $supplierAccount = AccountResolver::getSupplierAccount($voucher?->supplier);
                    $debitAccount = ($oldStatus === 'collected')
                        ? AccountResolver::getCashboxAccount($cashbox)
                        : AccountResolver::getChequesUnderCollectionAccount();

                    if ($supplierAccount && $debitAccount) {
                        JournalEntryLine::create([
                            'journal_entry_id' => $cancelJe->id,
                            'account_id' => $debitAccount->id,
                            'debit' => $cheque->amount,
                            'credit' => 0.00,
                            'description' => "عكس حساب الشيك الصادر الملغى رقم {$cheque->cheque_number}",
                        ]);
                        JournalEntryLine::create([
                            'journal_entry_id' => $cancelJe->id,
                            'account_id' => $supplierAccount->id,
                            'debit' => 0.00,
                            'credit' => $cheque->amount,
                            'description' => "إعادة المستحق للمورد نتيجة إلغاء الشيك الصادر رقم {$cheque->cheque_number}",
                        ]);
                    }
                }
            }

            $cheque->update([
                'status' => 'cancelled',
                'notes' => $notes,
            ]);

            // Sync connected invoice / purchase invoice status upon cheque cancellation
            $voucher = $cheque->voucher;
            if ($voucher) {
                $paymentService = app(\App\Services\PaymentService::class);
                if ($voucher->invoice) {
                    $paymentService->syncInvoicePaymentStatus($voucher->invoice);
                }
                if ($voucher->purchaseInvoice) {
                    $paymentService->syncPurchaseInvoicePaymentStatus($voucher->purchaseInvoice);
                }
            }

            ActivityLog::log(
                'cheque_cancelled',
                $cheque,
                "Cancelled cheque {$cheque->cheque_number}"
            );

        });

        return back()->with('info', 'تم إلغاء الشيك وإعادة القيد المحاسبي بنجاح.');
    }

    /**
     * Update status (Backward compatibility)
     */
    public function updateStatus(Request $request, $locale, Cheque $cheque)
    {
        $this->authorize('manage-cheques');

        $validated = $request->validate([
            'status' => ['required', 'in:received,under_collection,collected,returned,cancelled,deferred'],
            'notes' => ['nullable', 'string'],
        ]);

        if ($validated['status'] === 'collected' && $request->filled('cashbox_id')) {
            return $this->clear($request, $locale, $cheque);
        }

        if ($validated['status'] === 'returned') {
            return $this->bounce($request, $locale, $cheque);
        }

        if ($validated['status'] === 'cancelled') {
            return $this->cancel($request, $locale, $cheque);
        }

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


    /**
     * Print Cheque Deposit Slip (حافظة إيداع الشيكات)
     */
    public function depositSlip(Request $request)
    {
        $this->authorize('view-cheques');

        $chequeIds = $request->input('cheque_ids', []);
        if (is_string($chequeIds)) {
            $chequeIds = array_filter(explode(',', $chequeIds));
        }

        $query = Cheque::with(['voucher.customer', 'voucher.supplier', 'cashbox']);
        if (!empty($chequeIds)) {
            $query->whereIn('id', $chequeIds);
        } else {
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            } else {
                $query->pending();
            }
        }

        $cheques = $query->get();
        $totalAmount = $cheques->sum('amount');

        return view('finance.cheques.deposit_slip', compact('cheques', 'totalAmount'));
    }
}
