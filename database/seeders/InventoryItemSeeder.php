<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\Unit;
use App\Models\Warehouse;
use App\Models\WarehouseItem;

class InventoryItemSeeder extends Seeder
{
    public function run(): void
    {
        $piece = Unit::where('name', 'قطعة')->first() ?: Unit::create(['name' => 'قطعة', 'symbol' => 'قط', 'is_active' => true]);
        $carton = Unit::where('name', 'كرتونة')->first() ?: Unit::create(['name' => 'كرتونة', 'symbol' => 'كرت', 'is_active' => true]);
        $box = Unit::where('name', 'صندوق')->first() ?: Unit::create(['name' => 'صندوق', 'symbol' => 'صند', 'is_active' => true]);
        $doz = Unit::where('name', 'درزة (12 قطعة)')->first() ?: Unit::create(['name' => 'درزة (12 قطعة)', 'symbol' => 'دزن', 'is_active' => true]);

        $mainWarehouse = Warehouse::first() ?: Warehouse::create(['name' => 'المخزن الرئيسي', 'code' => 'WH-MAIN', 'is_active' => true]);

        $itemsByCategory = [
            'CAT-FOOD' => [
                [
                    'name' => 'زيت طعام عافية 1 لتر',
                    'item_code' => 'ITM-FD-001',
                    'barcode' => '628100100201',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 12,
                    'cost_price' => 18.50,
                    'retail_price' => 25.00,
                    'wholesale_price' => 22.00,
                    'min_stock_alert' => 50,
                    'initial_qty' => 120,
                ],
                [
                    'name' => 'سكر نقي زنة 10 كجم',
                    'item_code' => 'ITM-FD-002',
                    'barcode' => '628100100202',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 5,
                    'cost_price' => 45.00,
                    'retail_price' => 60.00,
                    'wholesale_price' => 52.00,
                    'min_stock_alert' => 20,
                    'initial_qty' => 100,
                ],
                [
                    'name' => 'شاي ناعم ممتاز 400 جرام',
                    'item_code' => 'ITM-FD-003',
                    'barcode' => '628100100203',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 24,
                    'cost_price' => 12.00,
                    'retail_price' => 16.00,
                    'wholesale_price' => 14.00,
                    'min_stock_alert' => 48,
                    'initial_qty' => 240,
                ],
            ],
            'CAT-CLEAN' => [
                [
                    'name' => 'مسحوق غسيل أوتوماتيك 5 كجم',
                    'item_code' => 'ITM-CL-001',
                    'barcode' => '628200100301',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 4,
                    'cost_price' => 38.00,
                    'retail_price' => 50.00,
                    'wholesale_price' => 44.00,
                    'min_stock_alert' => 15,
                    'initial_qty' => 80,
                ],
                [
                    'name' => 'سائل غسيل الأواني 1 لتر',
                    'item_code' => 'ITM-CL-002',
                    'barcode' => '628200100302',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 12,
                    'cost_price' => 8.50,
                    'retail_price' => 13.00,
                    'wholesale_price' => 10.50,
                    'min_stock_alert' => 36,
                    'initial_qty' => 180,
                ],
            ],
            'CAT-ELEC' => [
                [
                    'name' => 'شاشة تلفزيون سمارت 55 بوصة 4K',
                    'item_code' => 'ITM-EL-001',
                    'barcode' => '628300100401',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $box->id,
                    'conversion_factor' => 2,
                    'cost_price' => 1450.00,
                    'retail_price' => 1850.00,
                    'wholesale_price' => 1650.00,
                    'min_stock_alert' => 5,
                    'initial_qty' => 25,
                ],
                [
                    'name' => 'مروحة سقف 56 بوصة فاخرة',
                    'item_code' => 'ITM-EL-002',
                    'barcode' => '628300100402',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 4,
                    'cost_price' => 110.00,
                    'retail_price' => 155.00,
                    'wholesale_price' => 130.00,
                    'min_stock_alert' => 10,
                    'initial_qty' => 60,
                ],
                [
                    'name' => 'لمبة ليد 12 واط موفرة للطاقة',
                    'item_code' => 'ITM-EL-003',
                    'barcode' => '628300100403',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 50,
                    'cost_price' => 4.50,
                    'retail_price' => 8.00,
                    'wholesale_price' => 6.00,
                    'min_stock_alert' => 100,
                    'initial_qty' => 500,
                ],
            ],
            'CAT-GEN' => [
                [
                    'name' => 'دفتر ملاحظات A4 سلك 100 ورقة',
                    'item_code' => 'ITM-GN-001',
                    'barcode' => '628400100501',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 20,
                    'cost_price' => 6.00,
                    'retail_price' => 10.00,
                    'wholesale_price' => 8.00,
                    'min_stock_alert' => 40,
                    'initial_qty' => 200,
                ],
                [
                    'name' => 'طقم أقلام حبر جاف أزرق (10 أقلام)',
                    'item_code' => 'ITM-GN-002',
                    'barcode' => '628400100502',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $doz->id,
                    'conversion_factor' => 12,
                    'cost_price' => 12.00,
                    'retail_price' => 18.00,
                    'wholesale_price' => 15.00,
                    'min_stock_alert' => 24,
                    'initial_qty' => 144,
                ],
            ],
            'CAT-PARTS' => [
                [
                    'name' => 'مفصلات أثاث هيدروليك 35 ملم',
                    'item_code' => 'ITM-PR-001',
                    'barcode' => '628500100601',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 100,
                    'cost_price' => 3.00,
                    'retail_price' => 5.50,
                    'wholesale_price' => 4.00,
                    'min_stock_alert' => 200,
                    'initial_qty' => 1000,
                ],
                [
                    'name' => 'مسامير خشابي 4×40 ملم (علبة 500 مسمار)',
                    'item_code' => 'ITM-PR-002',
                    'barcode' => '628500100602',
                    'base_unit_id' => $piece->id,
                    'wholesale_unit_id' => $carton->id,
                    'conversion_factor' => 20,
                    'cost_price' => 15.00,
                    'retail_price' => 24.00,
                    'wholesale_price' => 19.00,
                    'min_stock_alert' => 30,
                    'initial_qty' => 150,
                ],
            ],
        ];

        foreach ($itemsByCategory as $catCode => $items) {
            $cat = ItemCategory::where('code', $catCode)->first();
            if (!$cat) continue;

            foreach ($items as $itemData) {
                $initialQty = $itemData['initial_qty'] ?? 100;
                unset($itemData['initial_qty']);
                
                $itemData['category_id'] = $cat->id;
                $itemData['is_active'] = true;

                $item = InventoryItem::updateOrCreate(
                    ['item_code' => $itemData['item_code']],
                    $itemData
                );

                WarehouseItem::updateOrCreate(
                    [
                        'warehouse_id' => $mainWarehouse->id,
                        'inventory_item_id' => $item->id,
                    ],
                    [
                        'qty_in_base_units' => $initialQty,
                    ]
                );
            }
        }
    }
}
