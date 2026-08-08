<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Customer;
use App\Models\Supplier;
use App\Models\Service;
use App\Models\Branch;

class CustomerSupplierServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $mainBranch = Branch::where('code', 'MAIN')->first();

        // 1. Seed Sample Customers
        Customer::firstOrCreate(
            ['code' => 'CUST-00001'],
            [
                'type' => 'company',
                'name' => 'شركة الأعمال المتقدمة للمقاولات',
                'company_name' => 'شركة الأعمال المتقدمة المحدودة',
                'phone' => '0912345678',
                'phone_secondary' => '0183456789',
                'email' => 'info@advanced-corp.sd',
                'address' => 'شارع الستين، الرياض',
                'city' => 'الخرطوم',
                'cr_number' => '1010123456',
                'vat_number' => '300012345600003',
                'credit_limit' => 500000.00,
                'credit_period_days' => 30,
                'category' => 'corporate',
                'is_active' => true,
                'notes' => 'عميل شركات مميز بخصم خاص',
                'branch_id' => $mainBranch?->id,
            ]
        );

        Customer::firstOrCreate(
            ['code' => 'CUST-00002'],
            [
                'type' => 'individual',
                'name' => 'محمد أحمد عبد الله',
                'phone' => '0959876543',
                'email' => 'm.abdallah@example.com',
                'address' => 'حي الملازمين',
                'city' => 'أم درمان',
                'credit_limit' => 50000.00,
                'credit_period_days' => 7,
                'category' => 'vip',
                'is_active' => true,
                'notes' => 'عميل أثاث منازل وفلل',
                'branch_id' => $mainBranch?->id,
            ]
        );

        // 2. Seed Sample Suppliers
        Supplier::firstOrCreate(
            ['code' => 'SUPP-00001'],
            [
                'name' => 'شركة مصانع الخشب والأكريليك',
                'company_name' => 'مصنع الأخشاب الوطنية',
                'contact_person' => 'المهندس طارق خالد',
                'phone' => '0941122334',
                'email' => 'sales@woodfactory.sd',
                'address' => 'المنطقة الصناعية',
                'city' => 'بحري',
                'cr_number' => '1010654321',
                'vat_number' => '310098765400003',
                'services_provided' => 'أشباه ألواح MDF، خشب زان، أكريليك 3 ملم و5 ملم، مفصلات واكسسوارات أثاث',
                'rating' => 5,
                'is_active' => true,
                'notes' => 'مورد ممتاز وسريع التوريد',
                'branch_id' => $mainBranch?->id,
            ]
        );

        // 3. Seed Standard Services
        $services = [
            [
                'code' => 'SRV-0001',
                'name_ar' => 'قص وتفريغ أخشاب CNC',
                'name_en' => 'CNC Wood Cutting & Engraving',
                'service_type' => 'cnc_cutting',
                'default_price' => 1500.00,
                'unit_of_measure' => 'm2',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'قص أخشاب MDF وألواح الملامين بالـ CNC الدقيقة حسب الخرائط الهندسية',
            ],
            [
                'code' => 'SRV-0002',
                'name_ar' => 'تصنيع خزائن وأثاث خشبي مخصص',
                'name_en' => 'Custom Wooden Furniture Fabrication',
                'service_type' => 'furniture_manufacturing',
                'default_price' => 12000.00,
                'unit_of_measure' => 'm',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تصنيع وتجميع الخزائن والدواليب والمكاتب الفاخرة',
            ],
            [
                'code' => 'SRV-0003',
                'name_ar' => 'تجهيز وتغليف ديكورات داخلية',
                'name_en' => 'Interior Decoration Outfitting',
                'service_type' => 'outfitting',
                'default_price' => 25000.00,
                'unit_of_measure' => 'job',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تجهيز وتطبيق التشطيبات والديكورات الجدارية والخشبيات',
            ],
            [
                'code' => 'SRV-0004',
                'name_ar' => 'أعمال مقاولات وتشطيبات موقعية',
                'name_en' => 'Site Contracting & Finishing Works',
                'service_type' => 'contracting',
                'default_price' => 50000.00,
                'unit_of_measure' => 'project',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'إدارة وتنفيذ مقاولات المعارض والمحلات التجارية',
            ],
            [
                'code' => 'SRV-0005',
                'name_ar' => 'تصنيع لوحات ولافتات إعلانية بارزة',
                'name_en' => '3D Advertising Signage Fabrication',
                'service_type' => 'signage',
                'default_price' => 3500.00,
                'unit_of_measure' => 'm2',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تصنيع وتجميع الحروف البارزة واللافتات المضاءة LED',
            ],
            [
                'code' => 'SRV-0006',
                'name_ar' => 'خدمة النقل والتوصيل للموقع',
                'name_en' => 'Transport & Delivery Service',
                'service_type' => 'transport',
                'default_price' => 2000.00,
                'unit_of_measure' => 'job',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'نقل المنتجات والأثاث بسيارات الورشة المجهزة',
            ],
            [
                'code' => 'SRV-0007',
                'name_ar' => 'خدمة التركيب والتشغيل الموقعي',
                'name_en' => 'On-site Installation Service',
                'service_type' => 'installation',
                'default_price' => 3000.00,
                'unit_of_measure' => 'hour',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'تركيب وتثبيت الأثاث واللافتات والمقاولات في موقع العميل',
            ],
            [
                'code' => 'SRV-0008',
                'name_ar' => 'خدمات التصميم الهندسي وإعداد ملفات CNC',
                'name_en' => 'Engineering Design & CNC File Preparation',
                'service_type' => 'design',
                'default_price' => 2500.00,
                'unit_of_measure' => 'hour',
                'is_taxable' => true,
                'is_active' => true,
                'description' => 'إعداد المخططات ثلاثية الأبعاد وبرمجة ملفات الـ AutoCAD والـ ArtCAM للـ CNC',
            ],
        ];

        foreach ($services as $serviceData) {
            Service::firstOrCreate(
                ['code' => $serviceData['code']],
                $serviceData
            );
        }
    }
}
