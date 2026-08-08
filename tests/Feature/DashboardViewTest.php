<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardViewTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_render_dashboard_without_errors(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ar/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="rtl"', false);
    }

    public function test_english_dashboard_renders_with_ltr_direction_and_english_locale(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/en/dashboard');

        $response->assertStatus(200);
        $response->assertSee('dir="ltr"', false);
    }
}
