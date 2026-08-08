<?php

namespace Database\Seeders;

use App\Models\Unit;
use Illuminate\Database\Seeder;

class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $units = [
            ['name' => 'قطعة', 'symbol' => 'قط', 'is_active' => true],
            ['name' => 'كرتونة', 'symbol' => 'كرت', 'is_active' => true],
            ['name' => 'صندوق', 'symbol' => 'صند', 'is_active' => true],
            ['name' => 'طرد', 'symbol' => 'طرد', 'is_active' => true],
            ['name' => 'درزة (12 قطعة)', 'symbol' => 'دزن', 'is_active' => true],
            ['name' => 'كيلوجرام', 'symbol' => 'كجم', 'is_active' => true],
            ['name' => 'متر', 'symbol' => 'م', 'is_active' => true],
            ['name' => 'لتر', 'symbol' => 'لتر', 'is_active' => true],
        ];

        foreach ($units as $u) {
            Unit::firstOrCreate(['name' => $u['name']], $u);
        }
    }
}
