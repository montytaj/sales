<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehouseSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'name' => 'المخزن الرئيسي (المركز الرئيسي)',
                'code' => 'WH-MAIN-01',
                'keeper_name' => 'محمد أحمد القحطاني',
                'phone' => '0501234567',
                'address' => 'الرياض - المنطقة الصناعية الأولى - الشارع العام',
                'notes' => 'المستودع الرئيسي لاستلام البضائع بالجملة والتوزيع',
                'is_active' => true,
            ],
            [
                'name' => 'مخزن التوزيع الفرعي (شمال الرياض)',
                'code' => 'WH-NORTH-02',
                'keeper_name' => 'خالد عبد الله السعيد',
                'phone' => '0559876543',
                'address' => 'الرياض - حي الياسمين - شارع التخصصي',
                'notes' => 'مستودع التوزيع السريع ومبيعات التجزئة والفرادي',
                'is_active' => true,
            ],
        ];

        foreach ($warehouses as $wh) {
            Warehouse::updateOrCreate(['code' => $wh['code']], $wh);
        }
    }
}
