<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Cashbox;
use App\Models\Branch;
use App\Models\User;

class CashboxAndPaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = Branch::where('is_main', true)->first();
        $admin = User::where('email', 'admin@example.com')->first();

        $mainCashbox = Cashbox::firstOrCreate(
            ['code' => 'CB-0001'],
            [
                'name_ar' => 'الخزنة الرئيسية (المكتب الرئيسي)',
                'name_en' => 'Main Cashbox',
                'branch_id' => $mainBranch?->id,
                'opening_balance' => 50000.00,
                'current_balance' => 50000.00,
                'is_active' => true,
            ]
        );

        $salesCashbox = Cashbox::firstOrCreate(
            ['code' => 'CB-0002'],
            [
                'name_ar' => 'صندوق صالة المبيعات والمعرض',
                'name_en' => 'Sales Showroom Cashbox',
                'branch_id' => $mainBranch?->id,
                'opening_balance' => 10000.00,
                'current_balance' => 10000.00,
                'is_active' => true,
            ]
        );

        if ($admin) {
            $mainCashbox->users()->syncWithoutDetaching([$admin->id]);
            $salesCashbox->users()->syncWithoutDetaching([$admin->id]);
        }
    }
}
