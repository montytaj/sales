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
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name_ar');
            $table->string('name_en')->nullable();
            $table->enum('service_type', [
                'cnc_cutting',
                'furniture_manufacturing',
                'outfitting',
                'contracting',
                'signage',
                'transport',
                'installation',
                'design'
            ]);
            $table->decimal('default_price', 15, 2)->default(0.00);
            $table->enum('unit_of_measure', ['m2', 'm', 'piece', 'hour', 'project', 'job', 'kg', 'set'])->default('piece');
            $table->boolean('is_taxable')->default(true);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['service_type', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
