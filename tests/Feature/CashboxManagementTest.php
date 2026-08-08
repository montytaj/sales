<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Cashbox;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CashboxManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(\Database\Seeders\DatabaseSeeder::class);

        $this->admin = User::where('email', 'admin@example.com')->first();
    }

    public function test_authorized_user_can_view_and_create_cashbox(): void
    {
        $response = $this->actingAs($this->admin)->get('/ar/cashboxes');
        $response->assertStatus(200);

        $response = $this->actingAs($this->admin)->post('/ar/cashboxes', [
            'name_ar' => 'خزنة المعرض الفرعية',
            'opening_balance' => 5000.00,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('cashboxes', [
            'name_ar' => 'خزنة المعرض الفرعية',
            'current_balance' => 5000.00,
        ]);
    }

    public function test_user_can_open_and_close_shift(): void
    {
        $cashbox = Cashbox::first();

        // Open shift
        $response = $this->actingAs($this->admin)->post('/ar/cashboxes/' . $cashbox->id . '/open-shift');
        $response->assertRedirect();

        $this->assertNotNull($cashbox->activeShift());

        // Close shift
        $response = $this->actingAs($this->admin)->post('/ar/cashboxes/' . $cashbox->id . '/close-shift', [
            'actual_closing_balance' => $cashbox->current_balance,
        ]);

        $response->assertRedirect();
        $this->assertNull($cashbox->activeShift());
    }

    public function test_cashbox_transfer_between_two_cashboxes(): void
    {
        $cashbox1 = Cashbox::find(1);
        $cashbox2 = Cashbox::find(2);

        $c1Initial = $cashbox1->current_balance;
        $c2Initial = $cashbox2->current_balance;

        $response = $this->actingAs($this->admin)->post('/ar/cashboxes/' . $cashbox1->id . '/transfer', [
            'target_cashbox_id' => $cashbox2->id,
            'amount' => 1000.00,
        ]);

        $response->assertRedirect();
        $cashbox1->refresh();
        $cashbox2->refresh();

        $this->assertEquals($c1Initial - 1000.00, $cashbox1->current_balance);
        $this->assertEquals($c2Initial + 1000.00, $cashbox2->current_balance);
    }
}
