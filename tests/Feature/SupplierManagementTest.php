<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Supplier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SupplierManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_suppliers(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/suppliers');

        $response->assertStatus(200);
        $response->assertSee('شركة مصانع الخشب والأكريليك');
    }

    public function test_admin_can_create_supplier(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/suppliers', [
            'name' => 'مصنع الألومنيوم والدهانات',
            'company_name' => 'مصنع الألومنيوم السعودي',
            'contact_person' => 'المهندس ياسر',
            'phone' => '0533334444',
            'email' => 'info@aluminum.sa',
            'address' => 'المنطقة الصناعية',
            'city' => 'الرياض',
            'services_provided' => 'قطاعات ألومنيوم دهان حراري واكسسوارات',
            'rating' => 4,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/ar/suppliers');
        $this->assertDatabaseHas('suppliers', [
            'name' => 'مصنع الألومنيوم والدهانات',
            'phone' => '0533334444',
        ]);
    }
}
