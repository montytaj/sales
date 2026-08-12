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

            // Check closed fiscal period & global lock date
            $this->validatePeriodNotLocked($entryDate);

            $fiscalPeriod = FiscalPeriod::where('start_date', '<=', $entryDate)
                ->where('end_date', '>=', $entryDate)
                ->first();

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

    /**
     * Calculate Cost of Goods Sold (COGS)
     * Formula: COGS = Beginning Inventory + Purchases - Purchase Returns - Ending Inventory
     */
    public function calculateCogs(float $beginningInventory, float $purchases, float $purchaseReturns, float $endingInventory): float
    {
        return max(0.0, ($beginningInventory + $purchases) - $purchaseReturns - $endingInventory);
    }

    /**
     * Calculate Depreciation amount based on standard accounting methods
     */
    public function calculateDepreciation(
        string $method,
        float $cost,
        float $salvageValue,
        int $lifespanYears,
        ?float $currentBookValue = null,
        ?float $unitsProduced = null,
        ?float $totalCapacity = null
    ): float {
        if ($lifespanYears <= 0) {
            throw new InvalidArgumentException("العمر الإنتاجي يجب أن يكون أكبر من صفر.");
        }

        $depreciableBase = max(0.0, $cost - $salvageValue);

        switch ($method) {
            case 'straight_line':
                // طريقة القسط الثابت = (التكلفة - الخردة) / العمر الإنتاجي
                return round($depreciableBase / $lifespanYears, 2);

            case 'declining_balance':
                // طريقة القسط المتناقص = (2 / العمر الإنتاجي) * القيمة الدفترية الحالية
                $bookValue = $currentBookValue ?? $cost;
                $rate = 2 / $lifespanYears;
                $depreciation = $bookValue * $rate;
                return round(min($depreciation, max(0.0, $bookValue - $salvageValue)), 2);

            case 'units_of_production':
                // طريقة وحدات الإنتاج = ((التكلفة - الخردة) / إجمالي الطاقة) * الوحدات المنتجة
                if (!$totalCapacity || $totalCapacity <= 0 || !$unitsProduced) {
                    throw new InvalidArgumentException("طريقة وحدات الإنتاج تتطلب إدخال الطاقة الإنتاجية والوحدات المنتجة بشكل صحيح.");
                }
                $ratePerUnit = $depreciableBase / $totalCapacity;
                return round($ratePerUnit * $unitsProduced, 2);

            default:
                throw new InvalidArgumentException("طريقة الإهلاك غير مدعومة: {$method}");
        }
    }

    /**
     * Create adjusting entry for Depreciation
     * Debit: Depreciation Expense Account
     * Credit: Accumulated Depreciation Account
     */
    public function createDepreciationJournalEntry(
        string $entryDate,
        int $depreciationExpenseAccountId,
        int $accumulatedDepreciationAccountId,
        float $amount,
        string $description = 'قيد إهلاك أصول ثابتة',
        bool $autoPost = false
    ): JournalEntry {
        $lines = [
            [
                'account_id' => $depreciationExpenseAccountId,
                'debit' => $amount,
                'credit' => 0.0,
                'description' => "من حـ/ مصروف الإهلاك - {$description}",
            ],
            [
                'account_id' => $accumulatedDepreciationAccountId,
                'debit' => 0.0,
                'credit' => $amount,
                'description' => "إلى حـ/ مجمع الإهلاك - {$description}",
            ]
        ];

        return $this->createJournalEntry($entryDate, $description, $lines, 'depreciation_adjustment', null, $autoPost);
    }

    /**
     * Create adjusting entry for Inventory Deficit / Loss / Shrinkage
     * Debit: Inventory Loss Account (خسائر مخزون تالف / مفقود)
     * Credit: Inventory Account (المخزون)
     */
    public function createInventoryAdjustmentJournalEntry(
        string $entryDate,
        int $inventoryAccountId,
        int $lossAccountId,
        float $amount,
        string $description = 'قيد تسوية جردية - عجز/تلف مخزون',
        bool $autoPost = false
    ): JournalEntry {
        $lines = [
            [
                'account_id' => $lossAccountId,
                'debit' => $amount,
                'credit' => 0.0,
                'description' => "من حـ/ خسائر مخزون (تالف + مفقود) - {$description}",
            ],
            [
                'account_id' => $inventoryAccountId,
                'debit' => 0.0,
                'credit' => $amount,
                'description' => "إلى حـ/ المخزون - {$description}",
            ]
        ];

        return $this->createJournalEntry($entryDate, $description, $lines, 'inventory_adjustment', null, $autoPost);
    }

    /**
     * Validate that entry date is not within a closed period or prior to lock date.
     */
    public function validatePeriodNotLocked(string $entryDate): void
    {
        $lockDate = setting('fiscal_year_lock_date');
        if ($lockDate && $entryDate <= $lockDate) {
            throw new InvalidArgumentException("لا يمكن إجراء أي عمليات أو تعديلات محاسبية في فترة مالية مغلقة (الفترة مغلقة حتى تاريخ {$lockDate}).");
        }

        $fiscalPeriod = FiscalPeriod::where('start_date', '<=', $entryDate)
            ->where('end_date', '>=', $entryDate)
            ->first();

        if ($fiscalPeriod && $fiscalPeriod->is_closed) {
            throw new InvalidArgumentException("لا يمكن إجراء عمليات محاسبية في فترة مالية مغلقة ({$fiscalPeriod->period_name}).");
        }
    }
}

