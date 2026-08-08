<?php

namespace App\Services;

use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use App\Models\FiscalPeriod;
use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class AccountingService
{
    /**
     * Create a double-entry journal entry.
     * Enforces sum(debit) == sum(credit).
     */
    public function createJournalEntry(
        string $entryDate,
        string $description,
        array $lines,
        ?string $referenceType = null,
        ?int $referenceId = null,
        bool $autoPost = false
    ): JournalEntry {
        return DB::transaction(function () use ($entryDate, $description, $lines, $referenceType, $referenceId, $autoPost) {
            $totalDebit = 0.0;
            $totalCredit = 0.0;

            foreach ($lines as $line) {
                $totalDebit += (float) ($line['debit'] ?? 0.0);
                $totalCredit += (float) ($line['credit'] ?? 0.0);
            }

            if (round($totalDebit, 2) !== round($totalCredit, 2)) {
                throw new InvalidArgumentException("القيد المحاسبي غير متوازن! إجمالي المدين (" . number_format($totalDebit, 2) . ") لا يساوي إجمالي الدائن (" . number_format($totalCredit, 2) . ").");
            }

            // Check closed fiscal period
            $fiscalPeriod = FiscalPeriod::where('start_date', '<=', $entryDate)
                ->where('end_date', '>=', $entryDate)
                ->first();

            if ($fiscalPeriod && $fiscalPeriod->is_closed) {
                throw new InvalidArgumentException("لا يمكن إضافة قيد محاسبي في فترة مالية مغلقة ({$fiscalPeriod->period_name}).");
            }

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            $entry = JournalEntry::create([
                'entry_number' => JournalEntry::generateEntryNumber(),
                'entry_date' => $entryDate,
                'fiscal_period_id' => $fiscalPeriod?->id,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'description' => $description,
                'status' => 'draft',
            ]);

            foreach ($lines as $line) {
                $entry->lines()->create([
                    'account_id' => $line['account_id'],
                    'cost_center_id' => $line['cost_center_id'] ?? null,
                    'debit' => $line['debit'] ?? 0.0,
                    'credit' => $line['credit'] ?? 0.0,
                    'description' => $line['description'] ?? $description,
                ]);
            }

            if ($autoPost) {
                $this->postJournalEntry($entry);
            }

            ActivityLog::log(
                'journal_entry_created',
                $entry,
                "Created journal entry {$entry->entry_number} for total SAR " . number_format($totalDebit, 2)
            );

            return $entry;
        });
    }

    /**
     * Post a journal entry and lock it against further modification.
     */
    public function postJournalEntry(JournalEntry $entry): void
    {
        DB::transaction(function () use ($entry) {
            if ($entry->status === 'posted') {
                throw new InvalidArgumentException("تم ترحيل هذا القيد المحاسبي مسبقاً ولا يمكن تكرار الترحيل.");
            }

            if ($entry->fiscalPeriod && $entry->fiscalPeriod->is_closed) {
                throw new InvalidArgumentException("لا يمكن ترحيل القيد في فترة مالية مغلقة.");
            }

            $userId = auth()->id() ?? User::first()?->id ?? 1;

            $entry->update([
                'status' => 'posted',
                'posted_by' => $userId,
                'posted_at' => now(),
            ]);

            ActivityLog::log(
                'journal_entry_posted',
                $entry,
                "Posted journal entry {$entry->entry_number}"
            );
        });
    }
}
