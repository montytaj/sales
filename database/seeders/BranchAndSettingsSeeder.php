<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Branch;
use App\Models\User;
use App\Services\SettingsService;

class BranchAndSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Create Main Branch
        $mainBranch = Branch::firstOrCreate(
            ['code' => 'MAIN'],
            [
                'name' => 'الفرع الرئيسي - الخرطوم',
                'address' => 'شارع الستين، الخرطوم',
                'phone' => '0912345678',
                'email' => 'main@example.com',
                'is_main' => true,
                'is_active' => true,
            ]
        );

        // 2. Create Second Branch
        $workshopBranch = Branch::firstOrCreate(
            ['code' => 'BR-01'],
            [
                'name' => 'فرع الورشة وأعمال CNC',
                'address' => 'المنطقة الصناعية، بحري',
                'phone' => '0918765432',
                'email' => 'workshop@example.com',
                'is_main' => false,
                'is_active' => true,
            ]
        );

        // Assign Main Branch to Admin User
        $admin = User::where('email', 'admin@example.com')->first();
        if ($admin) {
            $admin->main_branch_id = $mainBranch->id;
            $admin->save();
            $admin->branches()->syncWithoutDetaching([$mainBranch->id, $workshopBranch->id]);
        }

        // 3. Seed Default System Settings & Feature Flags
        app(SettingsService::class)->seedDefaults();
    }
}
