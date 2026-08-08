<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);

        $this->admin = User::factory()->create([
            'email' => 'admin_test@example.com',
            'is_active' => true,
        ]);
        $this->admin->assignRole('system-admin');
    }

    public function test_authorized_user_can_view_users_list(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/users');

        $response->assertStatus(200);
        $response->assertSee(__('users.users_list'));
    }

    public function test_unauthorized_user_cannot_view_users_list(): void
    {
        $regularUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($regularUser)->get('/ar/users');

        $response->assertStatus(403);
    }

    public function test_admin_can_create_new_user(): void
    {
        $response = $this->actingAs($this->admin)->post('/ar/users', [
            'name' => 'New Staff',
            'email' => 'staff@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => ['sales-rep'],
            'is_active' => 1,
        ]);

        $response->assertRedirect('/ar/users');
        $this->assertDatabaseHas('users', ['email' => 'staff@example.com', 'is_active' => true]);

        $createdUser = User::where('email', 'staff@example.com')->first();
        $this->assertTrue($createdUser->hasRole('sales-rep'));
    }

    public function test_deactivated_user_cannot_login(): void
    {
        $user = User::factory()->create([
            'password' => bcrypt('Password123!'),
            'is_active' => false,
        ]);

        $response = $this->post('/ar/login', [
            'email' => $user->email,
            'password' => 'Password123!',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_status_can_be_toggled(): void
    {
        $targetUser = User::factory()->create(['is_active' => true]);

        $response = $this->actingAs($this->admin)->patch('/ar/users/' . $targetUser->id . '/toggle-status');

        $response->assertRedirect();
        $this->assertFalse($targetUser->fresh()->is_active);
    }

    public function test_user_cannot_deactivate_self(): void
    {
        $response = $this->actingAs($this->admin)->patch('/ar/users/' . $this->admin->id . '/toggle-status');

        $response->assertSessionHas('error');
        $this->assertTrue($this->admin->fresh()->is_active);
    }

    public function test_non_admin_cannot_assign_system_admin_role(): void
    {
        $manager = User::factory()->create(['is_active' => true]);
        $manager->givePermissionTo(['create-users']);

        $response = $this->actingAs($manager)->post('/ar/users', [
            'name' => 'Attempted Admin',
            'email' => 'fakeadmin@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
            'roles' => ['system-admin'],
            'is_active' => 1,
        ]);

        $response->assertStatus(403);
    }
}
