<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;

class ItemCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'الأغذية والمشروبات', 'code' => 'CAT-FOOD', 'description' => 'تصنيف المواد الغذائية والمشروبات المحفوظة'],
            ['name' => 'المنظفات والمواد الاستهلاكية', 'code' => 'CAT-CLEAN', 'description' => 'تصنيف المنظفات والمستلزمات الاستهلاكية'],
            ['name' => 'الأجهزة والكهربائيات', 'code' => 'CAT-ELEC', 'description' => 'تصنيف المعدات والأدوات الكهربائية والإلكترونيات'],
            ['name' => 'البضائع والمنتجات العامة', 'code' => 'CAT-GEN', 'description' => 'تصنيف البضائع العامة والمنتجات التجارية المتنوعة'],
            ['name' => 'قطع الغيار والمستلزمات', 'code' => 'CAT-PARTS', 'description' => 'تصنيف قطع الغيار والمستلزمات الفنية والميكانيكية'],
        ];

        foreach ($categories as $cat) {
            ItemCategory::updateOrCreate(['code' => $cat['code']], $cat);
        }
    }
}
