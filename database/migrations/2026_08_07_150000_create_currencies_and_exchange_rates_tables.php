<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Currencies Table (العملات وأسعار الصرف)
        if (!Schema::hasTable('currencies')) {
            Schema::create('currencies', function (Blueprint $table) {
                $table->id();
                $table->string('code', 10)->unique(); // SAR, USD, EUR, SDG, AED...
                $table->string('name'); // ريال سعودي، دولار أمريكي...
                $table->string('symbol', 15)->nullable(); // ر.س، $، €...
                $table->decimal('exchange_rate', 15, 6)->default(1.000000); // سعر الصرف مقابل العملة الرئيسية
                $table->boolean('is_base')->default(false); // العملة الرئيسية للنظام
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Seed default currencies
            DB::table('currencies')->insert([
                ['code' => 'SAR', 'name' => 'ريال سعودي', 'symbol' => 'ر.س', 'exchange_rate' => 1.000000, 'is_base' => true, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'USD', 'name' => 'دولار أمريكي', 'symbol' => '$', 'exchange_rate' => 3.750000, 'is_base' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'EUR', 'name' => 'يورو', 'symbol' => '€', 'exchange_rate' => 4.050000, 'is_base' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'AED', 'name' => 'درهم إماراتي', 'symbol' => 'د.إ', 'exchange_rate' => 1.020000, 'is_base' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'EGP', 'name' => 'جنيه مصري', 'symbol' => 'ج.م', 'exchange_rate' => 0.077000, 'is_base' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
                ['code' => 'SDG', 'name' => 'جنيه سوداني', 'symbol' => 'ج.س', 'exchange_rate' => 0.006200, 'is_base' => false, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ]);
        }

        // 2. Add Multi-Currency columns to Invoices (المبيعات)
        if (Schema::hasTable('invoices')) {
            Schema::table('invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('invoices', 'currency_code')) {
                    $table->string('currency_code', 10)->default('SAR')->after('total_amount');
                }
                if (!Schema::hasColumn('invoices', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_code');
                }
                if (!Schema::hasColumn('invoices', 'base_currency_total')) {
                    $table->decimal('base_currency_total', 15, 2)->default(0.00)->after('exchange_rate');
                }
            });
        }

        // 3. Add Multi-Currency columns to Purchase Invoices (المشتريات)
        if (Schema::hasTable('purchase_invoices')) {
            Schema::table('purchase_invoices', function (Blueprint $table) {
                if (!Schema::hasColumn('purchase_invoices', 'currency_code')) {
                    $table->string('currency_code', 10)->default('SAR')->after('net_amount');
                }
                if (!Schema::hasColumn('purchase_invoices', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_code');
                }
                if (!Schema::hasColumn('purchase_invoices', 'base_currency_total')) {
                    $table->decimal('base_currency_total', 15, 2)->default(0.00)->after('exchange_rate');
                }
            });
        }

        // 4. Add Multi-Currency columns to Payment Vouchers (السندات المالية)
        if (Schema::hasTable('payment_vouchers')) {
            Schema::table('payment_vouchers', function (Blueprint $table) {
                if (!Schema::hasColumn('payment_vouchers', 'currency_code')) {
                    $table->string('currency_code', 10)->default('SAR')->after('amount');
                }
                if (!Schema::hasColumn('payment_vouchers', 'exchange_rate')) {
                    $table->decimal('exchange_rate', 15, 6)->default(1.000000)->after('currency_code');
                }
                if (!Schema::hasColumn('payment_vouchers', 'base_currency_amount')) {
                    $table->decimal('base_currency_amount', 15, 2)->default(0.00)->after('exchange_rate');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('currencies');
    }
};
