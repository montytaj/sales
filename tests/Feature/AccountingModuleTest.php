<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Account;
use App\Models\JournalEntry;
use App\Models\FiscalPeriod;
use App\Services\AccountingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountingModuleTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Account $cashAccount;
    protected Account $revenueAccount;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->cashAccount = Account::create(['code' => '1101', 'name' => 'الصندوق الرئيسي', 'type' => 'asset', 'is_active' => true]);
        $this->revenueAccount = Account::create(['code' => '4101', 'name' => 'إيرادات المبيعات', 'type' => 'revenue', 'is_active' => true]);
    }

    public function test_journal_entry_double_entry_balance_validation(): void
    {
        $accountingService = app(AccountingService::class);

        // Valid Balanced Entry (Debit == Credit)
        $entry = $accountingService->createJournalEntry('2026-08-01', 'قيد إثبات مبيعات نقدية', [
            ['account_id' => $this->cashAccount->id, 'debit' => 5000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 5000.00],
        ]);

        $this->assertDatabaseHas('journal_entries', ['entry_number' => $entry->entry_number, 'status' => 'draft']);

        // Unbalanced Entry (Fails)
        $this->expectException(\InvalidArgumentException::class);
        $accountingService->createJournalEntry('2026-08-01', 'قيد غير متوازن', [
            ['account_id' => $this->cashAccount->id, 'debit' => 5000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 4000.00],
        ]);
    }

    public function test_post_entry_and_prevent_double_posting(): void
    {
        $accountingService = app(AccountingService::class);

        $entry = $accountingService->createJournalEntry('2026-08-01', 'قيد سداد', [
            ['account_id' => $this->cashAccount->id, 'debit' => 1000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1000.00],
        ]);

        $accountingService->postJournalEntry($entry);
        $entry->refresh();
        $this->assertEquals('posted', $entry->status);

        // Double Posting (Fails)
        $this->expectException(\InvalidArgumentException::class);
        $accountingService->postJournalEntry($entry);
    }

    public function test_closed_fiscal_period_block(): void
    {
        $accountingService = app(AccountingService::class);

        FiscalPeriod::create([
            'period_name' => 'يناير 2026',
            'start_date' => '2026-01-01',
            'end_date' => '2026-01-31',
            'is_closed' => true,
        ]);

        $this->expectException(\InvalidArgumentException::class);
        $accountingService->createJournalEntry('2026-01-15', 'قيد بالفترة المغلقة', [
            ['account_id' => $this->cashAccount->id, 'debit' => 1000.00, 'credit' => 0.00],
            ['account_id' => $this->revenueAccount->id, 'debit' => 0.00, 'credit' => 1000.00],
        ]);
    }
}
