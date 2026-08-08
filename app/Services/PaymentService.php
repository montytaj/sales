<?php

namespace App\Services;

use App\Models\PaymentVoucher;
use App\Models\PaymentVoucherLine;
use App\Models\Cashbox;
use App\Models\Invoice;
use App\Models\Cheque;
use App\Models\ActivityLog;
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
                        'amount' => $line['amount'],
                        'issue_date' => $chequeData['issue_date'],
                        'due_date' => $chequeData['due_date'],
                        'status' => 'received',
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

            // 7. Auto Reconciliation of Invoice Status
            if (!empty($data['invoice_id']) && $data['type'] === 'receipt') {
                $invoice = Invoice::findOrFail($data['invoice_id']);
                $totalPaid = PaymentVoucher::where('invoice_id', $invoice->id)
                    ->where('status', 'completed')
                    ->sum('amount');

                if ($totalPaid >= $invoice->total_amount) {
                    $invoice->update(['status' => 'paid']);
                } elseif ($totalPaid > 0) {
                    $invoice->update(['status' => 'partially_paid']);
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

            // Recheck Invoice Status
            if ($voucher->invoice_id) {
                $invoice = Invoice::find($voucher->invoice_id);
                if ($invoice) {
                    $remainingPaid = PaymentVoucher::where('invoice_id', $invoice->id)
                        ->where('status', 'completed')
                        ->sum('amount');

                    if ($remainingPaid >= $invoice->total_amount) {
                        $invoice->update(['status' => 'paid']);
                    } elseif ($remainingPaid > 0) {
                        $invoice->update(['status' => 'partially_paid']);
                    } else {
                        $invoice->update(['status' => 'issued']);
                    }
                }
            }

            ActivityLog::log(
                'payment_voucher_cancelled',
                $voucher,
                "Cancelled voucher {$voucher->voucher_number}"
            );
        });
    }
}
