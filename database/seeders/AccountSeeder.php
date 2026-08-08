<?php

namespace Database\Seeders;

use App\Models\Account;
use Illuminate\Database\Seeder;

class AccountSeeder extends Seeder
{
    public function run(): void
    {
        $accounts = [
            // Level 1
            ['code' => '10000', 'name' => 'الأصول', 'parent_code' => null, 'level' => 1, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '20000', 'name' => 'الخصوم والالتزامات', 'parent_code' => null, 'level' => 1, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '30000', 'name' => 'حقوق الملكية', 'parent_code' => null, 'level' => 1, 'type' => 'equity', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '40000', 'name' => 'الإيرادات', 'parent_code' => null, 'level' => 1, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '50000', 'name' => 'المصروفات', 'parent_code' => null, 'level' => 1, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],

            // Level 2
            ['code' => '11000', 'name' => 'الأصول المتداولة', 'parent_code' => '10000', 'level' => 2, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '12000', 'name' => 'الأصول الثابتة', 'parent_code' => '10000', 'level' => 2, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '21000', 'name' => 'الالتزامات المتداولة', 'parent_code' => '20000', 'level' => 2, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '31000', 'name' => 'رأس المال والأرباح', 'parent_code' => '30000', 'level' => 2, 'type' => 'equity', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '41000', 'name' => 'إيرادات المبيعات', 'parent_code' => '40000', 'level' => 2, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '51000', 'name' => 'تكلفة المبيعات', 'parent_code' => '50000', 'level' => 2, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '52000', 'name' => 'المصروفات العمومية والإدارية', 'parent_code' => '50000', 'level' => 2, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],

            // Level 3
            ['code' => '11100', 'name' => 'النقدية وما في حكمها', 'parent_code' => '11000', 'level' => 3, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '11200', 'name' => 'ذمم العملاء والمدينون', 'parent_code' => '11000', 'level' => 3, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '11300', 'name' => 'المخزون', 'parent_code' => '11000', 'level' => 3, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '21100', 'name' => 'ذمم الموردين والدائنون', 'parent_code' => '21000', 'level' => 3, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '21200', 'name' => 'مصلحة الزكاة والدخل (الضريبة)', 'parent_code' => '21000', 'level' => 3, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '41100', 'name' => 'مبيعات البضائع والمنتجات', 'parent_code' => '41000', 'level' => 3, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '51100', 'name' => 'تكلفة البضاعة المباعة', 'parent_code' => '51000', 'level' => 3, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '52100', 'name' => 'مصروفات التشغيل والمرافق', 'parent_code' => '52000', 'level' => 3, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],

            // Level 4
            ['code' => '11110', 'name' => 'الصناديق والخزن', 'parent_code' => '11100', 'level' => 4, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '11120', 'name' => 'الحسابات البنكية', 'parent_code' => '11100', 'level' => 4, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '11210', 'name' => 'ذمم العملاء التجاريين', 'parent_code' => '11200', 'level' => 4, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '11310', 'name' => 'مخزون البضائع بغرض البيع', 'parent_code' => '11300', 'level' => 4, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '21110', 'name' => 'حسابات الموردين التجاريين', 'parent_code' => '21100', 'level' => 4, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '21210', 'name' => 'ضريبة القيمة المضافة المستحقة', 'parent_code' => '21200', 'level' => 4, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '41110', 'name' => 'مبيعات الجملة', 'parent_code' => '41100', 'level' => 4, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '41120', 'name' => 'مبيعات التجزئة والفرادي', 'parent_code' => '41100', 'level' => 4, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => false],
            ['code' => '51110', 'name' => 'تكاليف البضائع المباعة', 'parent_code' => '51100', 'level' => 4, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],
            ['code' => '52110', 'name' => 'المرافق والإيجارات', 'parent_code' => '52100', 'level' => 4, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => false],

            // Level 5 (Operational / Selectable level)
            ['code' => '111101', 'name' => 'الخزينة الرئيسية', 'parent_code' => '11110', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '111102', 'name' => 'خزينة المبيعات اليومية', 'parent_code' => '11110', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '111201', 'name' => 'بنك الراجحي الرئيسي', 'parent_code' => '11120', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '111202', 'name' => 'البنك الأهلي السعودي', 'parent_code' => '11120', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '112101', 'name' => 'حساب العملاء التجاريين - عام', 'parent_code' => '11210', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '113101', 'name' => 'حـ/ مخزون المركز الرئيسي', 'parent_code' => '11310', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '113102', 'name' => 'حـ/ مخزون المستودع الفرعي', 'parent_code' => '11310', 'level' => 5, 'type' => 'asset', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '211101', 'name' => 'حساب الموردين التجاريين - عام', 'parent_code' => '21110', 'level' => 5, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => true],
            ['code' => '212101', 'name' => 'حـ/ ضريبة القيمة المضافة (15%)', 'parent_code' => '21210', 'level' => 5, 'type' => 'liability', 'nature' => 'credit', 'is_selectable' => true],
            ['code' => '310001', 'name' => 'حـ/ رأس المال التجاري', 'parent_code' => '31000', 'level' => 5, 'type' => 'equity', 'nature' => 'credit', 'is_selectable' => true],
            ['code' => '411101', 'name' => 'حـ/ إيرادات مبيعات الجملة', 'parent_code' => '41110', 'level' => 5, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => true],
            ['code' => '411201', 'name' => 'حـ/ إيرادات مبيعات التجزئة', 'parent_code' => '41120', 'level' => 5, 'type' => 'revenue', 'nature' => 'credit', 'is_selectable' => true],
            ['code' => '511101', 'name' => 'حـ/ تكلفة البضاعة المباعة الإجمالية', 'parent_code' => '51110', 'level' => 5, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '521101', 'name' => 'حـ/ مصروفات الكهرباء والمياه', 'parent_code' => '52110', 'level' => 5, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => true],
            ['code' => '521102', 'name' => 'حـ/ مصروفات الإيجارات الرسمية', 'parent_code' => '52110', 'level' => 5, 'type' => 'expense', 'nature' => 'debit', 'is_selectable' => true],
        ];

        // First pass: create accounts or lookup existing
        $createdMap = [];
        foreach ($accounts as $acc) {
            $parentId = null;
            if (!empty($acc['parent_code']) && isset($createdMap[$acc['parent_code']])) {
                $parentId = $createdMap[$acc['parent_code']];
            } elseif (!empty($acc['parent_code'])) {
                $p = Account::where('code', $acc['parent_code'])->first();
                $parentId = $p?->id;
            }

            $account = Account::updateOrCreate(
                ['code' => $acc['code']],
                [
                    'name' => $acc['name'],
                    'parent_id' => $parentId,
                    'level' => $acc['level'],
                    'type' => $acc['type'],
                    'nature' => $acc['nature'],
                    'is_selectable' => $acc['is_selectable'],
                    'is_active' => true,
                ]
            );

            $createdMap[$acc['code']] = $account->id;
        }
    }
}
