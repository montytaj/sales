<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Branch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BranchManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected Branch $mainBranch;
    protected Branch $workshopBranch;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
        $this->mainBranch = Branch::where('code', 'MAIN')->first();
        $this->workshopBranch = Branch::where('code', 'BR-01')->first();
    }

    public function test_authorized_user_can_view_branches(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/branches');

        $response->assertStatus(200);
        $response->assertSee($this->mainBranch->name);
    }

    public function test_admin_can_create_branch(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/branches', [
            'code' => 'BR-02',
            'name' => 'فرع المعرض التجاري',
            'address' => 'جدة - شارع التحلية',
            'phone' => '0123456789',
            'is_main' => 0,
            'is_active' => 1,
        ]);

        $response->assertRedirect('/ar/branches');
        $this->assertDatabaseHas('branches', ['code' => 'BR-02', 'name' => 'فرع المعرض التجاري']);
    }

    public function test_user_branch_access_scoping(): void
    {
        $branchUser = User::factory()->create([
            'is_active' => true,
            'main_branch_id' => $this->workshopBranch->id,
        ]);
        $branchUser->assignRole('branch-manager');

        // User should have access to workshop branch
        $this->assertTrue($branchUser->hasAccessToBranch($this->workshopBranch->id));

        // User should NOT have access to main branch if not assigned
        $this->assertFalse($branchUser->hasAccessToBranch($this->mainBranch->id));

        // User attempting to view unauthorized branch receives 403
        $response = $this->actingAs($branchUser)->get('/ar/branches/' . $this->mainBranch->id);
        $response->assertStatus(403);
    }
}
