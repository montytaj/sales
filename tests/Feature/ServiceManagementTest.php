<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Service;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_services_catalog(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/services');

        $response->assertStatus(200);
        $response->assertSee('قص وتفريغ أخشاب CNC');
    }

    public function test_admin_can_create_service(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/services', [
            'name_ar' => 'خدمة التلميع والدهان الحراري',
            'name_en' => 'Polishing & Thermal Coating',
            'service_type' => 'outfitting',
            'default_price' => 450.00,
            'unit_of_measure' => 'm2',
            'is_taxable' => 1,
            'is_active' => 1,
            'description' => 'دهان أخشاب بالبخار والفرن الحراري',
        ]);

        $response->assertRedirect('/ar/services');
        $this->assertDatabaseHas('services', [
            'name_ar' => 'خدمة التلميع والدهان الحراري',
            'service_type' => 'outfitting',
        ]);
    }
}
