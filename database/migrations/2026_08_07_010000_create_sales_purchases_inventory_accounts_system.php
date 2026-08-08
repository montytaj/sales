<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Units of Measurement (وحدات القياس)
        if (!Schema::hasTable('units')) {
            Schema::create('units', function (Blueprint $table) {
                $table->id();
                $table->string('name'); // قطعة، كرتونة، صندوق...
                $table->string('symbol', 20)->nullable(); // قط، كرت، طرد...
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // 2. Enhance Item Categories (تصنيفات الأصناف)
        Schema::table('item_categories', function (Blueprint $table) {
            if (!Schema::hasColumn('item_categories', 'parent_id')) {
                $table->foreignId('parent_id')->nullable()->after('id')->constrained('item_categories')->nullOnDelete();
            }
            if (!Schema::hasColumn('item_categories', 'description')) {
                $table->text('description')->nullable()->after('code');
            }
            if (!Schema::hasColumn('item_categories', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        // 3. Enhance Warehouses (المخازن)
        Schema::table('warehouses', function (Blueprint $table) {
            if (!Schema::hasColumn('warehouses', 'keeper_name')) {
                $table->string('keeper_name')->nullable()->after('branch_id');
            }
            if (!Schema::hasColumn('warehouses', 'phone')) {
                $table->string('phone', 30)->nullable()->after('keeper_name');
            }
            if (!Schema::hasColumn('warehouses', 'address')) {
                $table->text('address')->nullable()->after('phone');
            }
            if (!Schema::hasColumn('warehouses', 'notes')) {
                $table->text('notes')->nullable()->after('address');
            }
        });

        // 4. Enhance Inventory Items for Multi-Units & Pricing
        Schema::table('inventory_items', function (Blueprint $table) {
            if (!Schema::hasColumn('inventory_items', 'code')) {
                $table->string('code', 50)->nullable()->after('name');
            }
            if (!Schema::hasColumn('inventory_items', 'barcode')) {
                $table->string('barcode', 100)->nullable()->unique()->after('code');
            }
            if (!Schema::hasColumn('inventory_items', 'base_unit_id')) {
                $table->foreignId('base_unit_id')->nullable()->after('unit')->constrained('units')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_items', 'wholesale_unit_id')) {
                $table->foreignId('wholesale_unit_id')->nullable()->after('base_unit_id')->constrained('units')->nullOnDelete();
            }
            if (!Schema::hasColumn('inventory_items', 'conversion_factor')) {
                $table->decimal('conversion_factor', 15, 4)->default(1.0000)->after('wholesale_unit_id');
            }
            if (!Schema::hasColumn('inventory_items', 'cost_price')) {
                $table->decimal('cost_price', 15, 2)->default(0.00)->after('conversion_factor');
            }
            if (!Schema::hasColumn('inventory_items', 'wholesale_price')) {
                $table->decimal('wholesale_price', 15, 2)->default(0.00)->after('cost_price');
            }
            if (!Schema::hasColumn('inventory_items', 'retail_price')) {
                $table->decimal('retail_price', 15, 2)->default(0.00)->after('wholesale_price');
            }
            if (!Schema::hasColumn('inventory_items', 'description')) {
                $table->text('description')->nullable()->after('retail_price');
            }
            if (!Schema::hasColumn('inventory_items', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('description');
            }
        });

        // 5. Warehouse Stock (مخزون الأصناف في المخازن)
        if (!Schema::hasTable('warehouse_items')) {
            Schema::create('warehouse_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->decimal('qty_in_base_units', 15, 4)->default(0.0000);
                $table->unique(['warehouse_id', 'inventory_item_id']);
                $table->timestamps();
            });
        }

        // 6. Stock Transfers (التحويلات بين المخازن)
        if (!Schema::hasTable('stock_transfers')) {
            Schema::create('stock_transfers', function (Blueprint $table) {
                $table->id();
                $table->string('transfer_number', 50)->unique();
                $table->foreignId('from_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->foreignId('to_warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->date('transfer_date');
                $table->enum('status', ['pending', 'completed', 'cancelled'])->default('completed');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_transfer_items')) {
            Schema::create('stock_transfer_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_transfer_id')->constrained('stock_transfers')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity', 15, 4);
                $table->decimal('qty_in_base_units', 15, 4);
                $table->timestamps();
            });
        }

        // 7. Stock Adjustments (تسويات المخزون)
        if (!Schema::hasTable('stock_adjustments')) {
            Schema::create('stock_adjustments', function (Blueprint $table) {
                $table->id();
                $table->string('adjustment_number', 50)->unique();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->date('adjustment_date');
                $table->enum('type', ['add', 'subtract']); // إضافة أو خصم
                $table->string('reason')->nullable();
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('stock_adjustment_items')) {
            Schema::create('stock_adjustment_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('stock_adjustment_id')->constrained('stock_adjustments')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity', 15, 4);
                $table->decimal('qty_in_base_units', 15, 4);
                $table->decimal('unit_cost', 15, 2)->default(0.00);
                $table->decimal('total_cost', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 8. 5-Level Chart of Accounts (شجرة الحسابات)
        if (!Schema::hasTable('accounts')) {
            Schema::create('accounts', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name');
                $table->foreignId('parent_id')->nullable()->constrained('accounts')->nullOnDelete();
                $table->tinyInteger('level')->default(1); // 1 to 5
                $table->enum('type', ['asset', 'liability', 'equity', 'revenue', 'expense']);
                $table->enum('nature', ['debit', 'credit'])->default('debit');
                $table->decimal('balance', 15, 2)->default(0.00);
                $table->boolean('is_selectable')->default(true); // المستوى 5 فقط يقبل القيود
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });
        }

        // Update journal_entry_lines foreign key to point to accounts table
        Schema::table('journal_entry_lines', function (Blueprint $table) {
            $table->dropForeign(['account_id']);
            $table->foreign('account_id')->references('id')->on('accounts')->cascadeOnDelete();
        });

        // 9. Enhance Purchase Invoices & Items
        Schema::table('purchase_invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('purchase_invoices', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('supplier_id')->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('purchase_invoices', 'payment_type')) {
                $table->enum('payment_type', ['cash', 'bank', 'credit'])->default('cash')->after('status');
            }
            if (!Schema::hasColumn('purchase_invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0.00)->after('tax_amount');
            }
            if (!Schema::hasColumn('purchase_invoices', 'notes')) {
                $table->text('notes')->nullable()->after('invoice_date');
            }
        });

        if (!Schema::hasTable('purchase_invoice_items')) {
            Schema::create('purchase_invoice_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_invoice_id')->constrained('purchase_invoices')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity', 15, 4);
                $table->decimal('qty_in_base_units', 15, 4);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('subtotal', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('total', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 10. Enhance Invoices (Sales Invoices) & Items
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'warehouse_id')) {
                $table->foreignId('warehouse_id')->nullable()->after('customer_id')->constrained('warehouses')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoices', 'payment_type')) {
                $table->enum('payment_type', ['cash', 'bank', 'credit'])->default('cash')->after('status');
            }
            if (!Schema::hasColumn('invoices', 'discount_amount')) {
                $table->decimal('discount_amount', 15, 2)->default(0.00)->after('tax_amount');
            }
        });

        Schema::table('invoice_items', function (Blueprint $table) {
            if (!Schema::hasColumn('invoice_items', 'inventory_item_id')) {
                $table->foreignId('inventory_item_id')->nullable()->after('invoice_id')->constrained('inventory_items')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoice_items', 'unit_id')) {
                $table->foreignId('unit_id')->nullable()->after('inventory_item_id')->constrained('units')->nullOnDelete();
            }
            if (!Schema::hasColumn('invoice_items', 'qty_in_base_units')) {
                $table->decimal('qty_in_base_units', 15, 4)->default(0.0000)->after('quantity');
            }
        });

        // 11. Sales Returns (مرتجعات المبيعات)
        if (!Schema::hasTable('sales_returns')) {
            Schema::create('sales_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 50)->unique();
                $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
                $table->foreignId('customer_id')->constrained('customers')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->date('return_date');
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('net_amount', 15, 2)->default(0.00);
                $table->enum('payment_type', ['cash', 'bank', 'credit'])->default('cash');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('sales_return_items')) {
            Schema::create('sales_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('sales_return_id')->constrained('sales_returns')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity', 15, 4);
                $table->decimal('qty_in_base_units', 15, 4);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('total', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }

        // 12. Purchase Returns (مرتجعات المشتريات)
        if (!Schema::hasTable('purchase_returns')) {
            Schema::create('purchase_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 50)->unique();
                $table->foreignId('purchase_invoice_id')->nullable()->constrained('purchase_invoices')->nullOnDelete();
                $table->foreignId('supplier_id')->constrained('suppliers')->cascadeOnDelete();
                $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
                $table->date('return_date');
                $table->decimal('total_amount', 15, 2)->default(0.00);
                $table->decimal('tax_amount', 15, 2)->default(0.00);
                $table->decimal('net_amount', 15, 2)->default(0.00);
                $table->enum('payment_type', ['cash', 'bank', 'credit'])->default('cash');
                $table->text('notes')->nullable();
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('purchase_return_items')) {
            Schema::create('purchase_return_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('purchase_return_id')->constrained('purchase_returns')->cascadeOnDelete();
                $table->foreignId('inventory_item_id')->constrained('inventory_items')->cascadeOnDelete();
                $table->foreignId('unit_id')->nullable()->constrained('units')->nullOnDelete();
                $table->decimal('quantity', 15, 4);
                $table->decimal('qty_in_base_units', 15, 4);
                $table->decimal('unit_price', 15, 2)->default(0.00);
                $table->decimal('total', 15, 2)->default(0.00);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_return_items');
        Schema::dropIfExists('purchase_returns');
        Schema::dropIfExists('sales_return_items');
        Schema::dropIfExists('sales_returns');
        Schema::dropIfExists('purchase_invoice_items');
        Schema::dropIfExists('accounts');
        Schema::dropIfExists('stock_adjustment_items');
        Schema::dropIfExists('stock_adjustments');
        Schema::dropIfExists('stock_transfer_items');
        Schema::dropIfExists('stock_transfers');
        Schema::dropIfExists('warehouse_items');
        Schema::dropIfExists('units');
    }
};
