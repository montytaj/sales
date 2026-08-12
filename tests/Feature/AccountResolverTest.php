<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Cashbox;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\User;
use App\Models\InventoryItem;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Services\AccountResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccountResolverTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $this->admin = User::first();
    }

    public function test_account_resolver_returns_default_accounts(): void
    {
        $salesAcc = AccountResolver::getSalesAccount();
        $this->assertNotNull($salesAcc);
        $this->assertEquals('revenue', $salesAcc->type);

        $vatAcc = AccountResolver::getVatAccount();
        $this->assertNotNull($vatAcc);
        $this->assertEquals('liability', $vatAcc->type);

        $arAcc = AccountResolver::getCustomerAccount();
        $this->assertNotNull($arAcc);
        $this->assertEquals('asset', $arAcc->type);

        $apAcc = AccountResolver::getSupplierAccount();
        $this->assertNotNull($apAcc);
        $this->assertEquals('liability', $apAcc->type);
    }

    public function test_model_account_id_binding_overrides_defaults(): void
    {
        $customCustomerAcc = Account::create([
            'code' => '999101',
            'name' => 'حساب عميل خاص جداً',
            'type' => 'asset',
            'nature' => 'debit',
            'is_selectable' => true,
            'is_active' => true,
        ]);

        $customer = Customer::first();
        $customer->update(['account_id' => $customCustomerAcc->id]);

        $resolvedAcc = AccountResolver::getCustomerAccount($customer);
        $this->assertEquals($customCustomerAcc->id, $resolvedAcc->id);
        $this->assertEquals('999101', $resolvedAcc->code);
    }

    public function test_changing_account_codes_does_not_break_invoice_journal_creation(): void
    {
        // Modify sales account code from 411101 to custom code 499999
        $salesAcc = Account::where('code', '411101')->first();
        if ($salesAcc) {
            $salesAcc->update(['code' => '499999', 'name' => 'إيرادات عامة معدلة']);
        }

        $customer = Customer::first();
        $customer->update(['credit_limit' => 1000000.00]);
        $warehouse = Warehouse::first();
        $item = InventoryItem::first();
        $unit = Unit::first();

        $invoiceData = [
            'customer_id' => $customer->id,
            'warehouse_id' => $warehouse->id,
            'issue_date' => now()->toDateString(),
            'payment_type' => 'credit',
            'items' => [
                [
                    'inventory_item_id' => $item->id,
                    'unit_id' => $unit->id,
                    'quantity' => 5,
                    'unit_price' => 100,
                ]
            ]
        ];

        $response = $this->actingAs($this->admin)->post('/ar/invoices', $invoiceData);
        $response->assertSessionHasNoErrors();
        $response->assertRedirect('/ar/invoices');

        $resolved = AccountResolver::getSalesAccount();
        $this->assertNotNull($resolved);
    }
}
