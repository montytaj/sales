<?php

namespace App\Services;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherLine;
use App\Models\Cashbox;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Invoice;
use App\Models\Cheque;
use App\Models\ActivityLog;
use App\Services\AccountResolver;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;


class PaymentService
{
    /**
     * Create a payment receipt or disbursement voucher.
     */
    public function createVoucher(array $data, array $paymentLines, array $chequeData = []): PaymentVoucher
    {
        return DB::transaction(function () use ($data, $paymentLines, $chequeData) {
            $totalAmount = (float) $data['amount'];

            // 1. Validate split payments line sum
            $linesSum = 0.0;
            foreach ($paymentLines as $line) {
                $linesSum += (float) $line['amount'];
            }

            if (abs($totalAmount - $linesSum) > 0.01) {
                throw new InvalidArgumentException("مجموع مبالغ وسائل الدفع ({$linesSum}) لا يساوي المبلغ الإجمالي للسند ({$totalAmount}).");
            }

            // 2. Validate Cashbox & negative balance restriction
            $cashbox = null;
            if (!empty($data['cashbox_id'])) {
                $cashbox = Cashbox::lockForUpdate()->findOrFail($data['cashbox_id']);
                $allowNegative = setting('allow_negative_cashbox', false);

                if ($data['type'] === 'payment' && !$allowNegative) {
                    if (($cashbox->current_balance - $totalAmount) < 0) {
                        throw new InvalidArgumentException("رصيد الخزنة الحالي ({$cashbox->current_balance}) لا يكفي لإتمام عملية الصرف بقيمة ({$totalAmount}).");
                    }
                }
            }

            // 3. Create Voucher
            $voucher = PaymentVoucher::create([
                'voucher_number' => PaymentVoucher::generateVoucherNumber($data['type']),
                'type' => $data['type'],
                'customer_id' => $data['customer_id'] ?? null,
                'supplier_id' => $data['supplier_id'] ?? null,
                'invoice_id' => $data['invoice_id'] ?? null,
                'purchase_invoice_id' => $data['purchase_invoice_id'] ?? null,
                'cashbox_id' => $data['cashbox_id'] ?? null,
                'target_cashbox_id' => $data['target_cashbox_id'] ?? null,
                'amount' => $totalAmount,
                'payment_date' => $data['payment_date'],
                'notes' => $data['notes'] ?? null,
                'status' => 'completed',
                'created_by' => auth()->id(),
            ]);

            // 4. Create Payment Lines
            foreach ($paymentLines as $line) {
                $voucherLine = $voucher->lines()->create($line);

                // If payment method is cheque, store in cheques table
                if ($line['payment_method'] === 'cheque' && !empty($chequeData)) {
                    Cheque::create([
                        'payment_voucher_id' => $voucher->id,
                        'cheque_number' => $chequeData['cheque_number'],
                        'bank_name' => $chequeData['bank_name'],
                        'drawer_name' => $chequeData['drawer_name'],
                        'payee_name' => $chequeData['payee_name'] ?? null,
                        'amount' => $line['amount'],
                        'issue_date' => $chequeData['issue_date'],
                        'due_date' => $chequeData['due_date'],
                        'status' => 'received',
                        'type' => ($data['type'] === 'payment') ? 'outgoing' : 'incoming',
                        'notes' => $chequeData['notes'] ?? null,
                        'created_by' => auth()->id(),
                    ]);
                }
            }

            // 5. Update Cashbox balance
            if ($cashbox) {
                if ($data['type'] === 'receipt') {
                    $cashbox->increment('current_balance', $totalAmount);
                } elseif ($data['type'] === 'payment') {
                    $cashbox->decrement('current_balance', $totalAmount);
                }
            }

            // 6. Handle Cashbox Transfers
            if ($data['type'] === 'transfer' && !empty($data['cashbox_id']) && !empty($data['target_cashbox_id'])) {
                $sourceCashbox = Cashbox::lockForUpdate()->findOrFail($data['cashbox_id']);
                $targetCashbox = Cashbox::lockForUpdate()->findOrFail($data['target_cashbox_id']);

                $allowNegative = setting('allow_negative_cashbox', false);
                if (!$allowNegative && ($sourceCashbox->current_balance - $totalAmount) < 0) {
                    throw new InvalidArgumentException("رصيد خزنة المصدر ({$sourceCashbox->current_balance}) لا يكفي لإجراء تحويل بقيمة ({$totalAmount}).");
                }

                $sourceCashbox->decrement('current_balance', $totalAmount);
                $targetCashbox->increment('current_balance', $totalAmount);
            }

            // 7. Auto Reconciliation of Sales Invoice Status
            if (!empty($data['invoice_id']) && $data['type'] === 'receipt') {
                $invoice = Invoice::findOrFail($data['invoice_id']);
                $this->syncInvoicePaymentStatus($invoice);
            }

            // 8. Auto Reconciliation of Purchase Invoice Status
            if (!empty($data['purchase_invoice_id']) && $data['type'] === 'payment') {
                $pInvoice = \App\Models\PurchaseInvoice::findOrFail($data['purchase_invoice_id']);
                $this->syncPurchaseInvoicePaymentStatus($pInvoice);
            }


            // 9. Create Accounting Journal Entry
            $accountantUser = auth()->id() ?? 1;
            $firstMethod = $paymentLines[0]['payment_method'] ?? 'cash';
            
            $cashbox = !empty($data['cashbox_id']) ? Cashbox::find($data['cashbox_id']) : null;
            $supplier = !empty($data['supplier_id']) ? Supplier::find($data['supplier_id']) : null;
            $customer = !empty($data['customer_id']) ? Customer::find($data['customer_id']) : null;

            if (in_array($firstMethod, ['bank', 'card', 'transfer'])) {
                $financialAccount = AccountResolver::getBankAccount();
            } elseif ($firstMethod === 'cheque') {
                $financialAccount = AccountResolver::getChequesUnderCollectionAccount();
            } else {
                $financialAccount = AccountResolver::getCashboxAccount($cashbox);
            }

            if ($data['type'] === 'payment') {
                $supplierAccount = AccountResolver::getSupplierAccount($supplier);

                if ($supplierAccount && $financialAccount) {
                    $je = \App\Models\JournalEntry::create([
                        'entry_number' => \App\Models\JournalEntry::generateEntryNumber(),
                        'entry_date' => $data['payment_date'],
                        'reference_type' => PaymentVoucher::class,
                        'reference_id' => $voucher->id,
                        'description' => "سداد للمورد بموجب سند صرف رقم " . $voucher->voucher_number,
                        'status' => 'posted',
                        'posted_by' => $accountantUser,
                        'posted_at' => now(),
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $supplierAccount->id,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'description' => "تسديد مستحقات آجل للمورد - سند صرف " . $voucher->voucher_number,
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $financialAccount->id,
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'description' => "صرف المبالغ بموجب سند صرف رقم " . $voucher->voucher_number,
                    ]);
                }
            } elseif ($data['type'] === 'receipt') {
                $customerAccount = AccountResolver::getCustomerAccount($customer);


                if ($customerAccount && $financialAccount) {
                    $je = \App\Models\JournalEntry::create([
                        'entry_number' => \App\Models\JournalEntry::generateEntryNumber(),
                        'entry_date' => $data['payment_date'],
                        'reference_type' => PaymentVoucher::class,
                        'reference_id' => $voucher->id,
                        'description' => "تحصيل من العميل بموجب سند قبض رقم " . $voucher->voucher_number,
                        'status' => 'posted',
                        'posted_by' => $accountantUser,
                        'posted_at' => now(),
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $financialAccount->id,
                        'debit' => $totalAmount,
                        'credit' => 0,
                        'description' => "تحصيل المبالغ - سند قبض " . $voucher->voucher_number,
                    ]);

                    \App\Models\JournalEntryLine::create([
                        'journal_entry_id' => $je->id,
                        'account_id' => $customerAccount->id,
                        'debit' => 0,
                        'credit' => $totalAmount,
                        'description' => "تحصيل مستحقات العميل - سند قبض " . $voucher->voucher_number,
                    ]);
                }
            }

            ActivityLog::log(
                'payment_voucher_created',
                $voucher,
                "Created {$voucher->type} voucher {$voucher->voucher_number} amount SAR {$voucher->amount}"
            );

            return $voucher;
        });
    }

    /**
     * Cancel payment voucher and revert cashbox/invoice balances.
     */
    public function cancelVoucher(PaymentVoucher $voucher): void
    {
        DB::transaction(function () use ($voucher) {
            if ($voucher->status === 'cancelled') {
                return;
            }

            // Revert Cashbox Balance
            if ($voucher->cashbox_id) {
                $cashbox = Cashbox::lockForUpdate()->find($voucher->cashbox_id);
                if ($cashbox) {
                    if ($voucher->type === 'receipt') {
                        $cashbox->decrement('current_balance', $voucher->amount);
                    } elseif ($voucher->type === 'payment') {
                        $cashbox->increment('current_balance', $voucher->amount);
                    }
                }
            }

            // Revert Transfer
            if ($voucher->type === 'transfer' && $voucher->cashbox_id && $voucher->target_cashbox_id) {
                $sourceCashbox = Cashbox::lockForUpdate()->find($voucher->cashbox_id);
                $targetCashbox = Cashbox::lockForUpdate()->find($voucher->target_cashbox_id);

                if ($sourceCashbox && $targetCashbox) {
                    $sourceCashbox->increment('current_balance', $voucher->amount);
                    $targetCashbox->decrement('current_balance', $voucher->amount);
                }
            }

            $voucher->update(['status' => 'cancelled']);

            // Recheck Sales Invoice Status
            if ($voucher->invoice_id) {
                $invoice = Invoice::find($voucher->invoice_id);
                if ($invoice) {
                    $this->syncInvoicePaymentStatus($invoice);
                }
            }

            // Recheck Purchase Invoice Status
            if ($voucher->purchase_invoice_id) {
                $pInvoice = \App\Models\PurchaseInvoice::find($voucher->purchase_invoice_id);
                if ($pInvoice) {
                    $this->syncPurchaseInvoicePaymentStatus($pInvoice);
                }
            }

            ActivityLog::log(
                'payment_voucher_cancelled',
                $voucher,
                "Cancelled voucher {$voucher->voucher_number}"
            );
        });
    }

    /**
     * Recalculate invoice paid amount, due_amount, and status.
     * Uncollected cheques DO NOT reduce due_amount or mark invoice as paid!
     */
    public function syncInvoicePaymentStatus(Invoice $invoice): void
    {
        $vouchers = PaymentVoucher::where('invoice_id', $invoice->id)
            ->whereIn('status', ['posted', 'completed'])
            ->with(['cheques'])
            ->get();

        $effectivePaid = 0.0;
        foreach ($vouchers as $voucher) {
            if ($voucher->cheques->count() > 0) {
                foreach ($voucher->cheques as $cheque) {
                    if ($cheque->status === 'collected') {
                        $effectivePaid += (float) $cheque->amount;
                    }
                }
            } else {
                $effectivePaid += (float) $voucher->amount;
            }
        }

        $totalAmount = (float) $invoice->total_amount;
        $dueAmount = max(0, $totalAmount - $effectivePaid);

        $status = 'issued';
        if ($dueAmount <= 0.001) {
            $status = 'paid';
            $dueAmount = 0.00;
        } elseif ($effectivePaid > 0) {
            $status = 'partially_paid';
        }

        $invoice->update([
            'due_amount' => $dueAmount,
            'status' => $status,
        ]);
    }

    /**
     * Recalculate purchase invoice paid amount, due_amount, and status.
     * Uncollected cheques DO NOT reduce due_amount or mark purchase invoice as paid!
     */
    public function syncPurchaseInvoicePaymentStatus(\App\Models\PurchaseInvoice $pInvoice): void
    {
        $initialPaid = (float) ($pInvoice->cash_amount + $pInvoice->bank_amount);

        $vouchers = PaymentVoucher::where('purchase_invoice_id', $pInvoice->id)
            ->whereIn('status', ['posted', 'completed'])
            ->with(['cheques'])
            ->get();

        $vouchersPaid = 0.0;
        foreach ($vouchers as $voucher) {
            if ($voucher->cheques->count() > 0) {
                foreach ($voucher->cheques as $cheque) {
                    if ($cheque->status === 'collected') {
                        $vouchersPaid += (float) $cheque->amount;
                    }
                }
            } else {
                $vouchersPaid += (float) $voucher->amount;
            }
        }

        $totalPaid = $initialPaid + $vouchersPaid;
        $netAmount = (float) $pInvoice->net_amount;
        $dueAmount = max(0, $netAmount - $totalPaid);

        $status = 'unpaid';
        if ($dueAmount <= 0.001) {
            $status = 'paid';
            $dueAmount = 0.00;
        } elseif ($totalPaid > 0) {
            $status = 'partially_paid';
        }

        $pInvoice->update([
            'due_amount' => $dueAmount,
            'status' => $status,
        ]);
    }
}

