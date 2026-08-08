<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_customers_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/customers');

        $response->assertStatus(200);
        $response->assertSee('شركة الأعمال المتقدمة للمقاولات');
    }

    public function test_admin_can_create_customer(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/customers', [
            'type' => 'company',
            'name' => 'شركة أثاث المستقبل',
            'company_name' => 'شركة أثاث المستقبل المحدودة',
            'phone' => '0599998888',
            'phone_secondary' => '0112223333',
            'email' => 'contact@futurewood.sa',
            'address' => 'شارع التخصصي',
            'city' => 'الرياض',
            'cr_number' => '1010998877',
            'vat_number' => '300099887700003',
            'credit_limit' => 25000.00,
            'credit_period_days' => 15,
            'category' => 'corporate',
            'is_active' => 1,
            'notes' => 'عميل معرض جديد',
        ]);

        $response->assertRedirect('/ar/customers');
        $this->assertDatabaseHas('customers', [
            'name' => 'شركة أثاث المستقبل',
            'phone' => '0599998888',
        ]);
    }

    public function test_phone_duplicate_prevention_warning(): void
    {
        $existingCustomer = Customer::first();

        $response = $this->actingAs($this->admin)->post('/ar/customers', [
            'type' => 'individual',
            'name' => 'عميل مكرر الهاتف',
            'phone' => $existingCustomer->phone,
            'credit_limit' => 0,
            'credit_period_days' => 0,
            'category' => 'regular',
        ]);

        $response->assertSessionHas('error');
    }

    public function test_unauthorized_user_cannot_access_customers(): void
    {
        $unauthorizedUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($unauthorizedUser)->get('/ar/customers');
        $response->assertStatus(403);
    }
}
