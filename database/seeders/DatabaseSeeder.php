<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create or Update Default Administrator User
        $admin = User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'System Administrator',
                'password' => Hash::make('Password123!'),
                'is_active' => true,
            ]
        );

        // 2. Call Seeders
        $this->call([
            RoleAndPermissionSeeder::class,
            BranchAndSettingsSeeder::class,
            UnitSeeder::class,
            ItemCategorySeeder::class,
            WarehouseSeeder::class,
            InventoryItemSeeder::class,
            AccountSeeder::class,
            CustomerSupplierServiceSeeder::class,
            CashboxAndPaymentSeeder::class,
            DemoDataSeeder::class,
        ]);
    }
}
