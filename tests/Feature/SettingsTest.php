<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\SettingsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_admin_can_view_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/settings');

        $response->assertStatus(200);
        $response->assertSee(__('settings.facility_name'));
    }

    public function test_admin_can_update_settings_and_feature_flags(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/settings', [
            'facility_name' => 'ورشة الأثاث المتقدمة ERP',
            'currency' => 'SAR',
            'timezone' => 'Asia/Riyadh',
            'default_locale' => 'ar',
            'tax_percentage' => '15.00',
            'number_format' => '2',
            'doc_prefix_quotation' => 'QUOT-',
            'doc_prefix_invoice' => 'INV-',
            'doc_prefix_work_order' => 'JOB-',
            'allow_negative_inventory' => 0,
            'allow_delivery_with_balance' => 0,
            'min_downpayment_percentage' => '50.00',
            'inventory_enabled' => 1,
            'accounting_enabled' => 1,
            'cheques_enabled' => 0, // Disable cheques
            'projects_enabled' => 1,
            'signage_enabled' => 1,
        ]);

        $response->assertRedirect();

        // Verify centralized settings service
        $this->assertEquals('ورشة الأثاث المتقدمة ERP', setting('facility_name'));
        $this->assertEquals('QUOT-', setting('doc_prefix_quotation'));
        $this->assertTrue(feature_enabled('inventory_enabled'));
        $this->assertFalse(feature_enabled('cheques_enabled'));
    }
}
