<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('category_id')->constrained('stock_categories');
            $table->string('name', 200)->index();
            $table->string('generic_name', 200)->nullable();
            $table->string('sku', 50)->unique()->nullable();
            $table->string('barcode', 50)->nullable()->index();
            $table->string('unit_of_measure', 30);
            $table->decimal('unit_price', 12, 2)->default(0);
            $table->decimal('cost_price', 12, 2)->default(0);
            $table->integer('reorder_level')->default(0);
            $table->boolean('is_prescription_only')->default(false);
            $table->boolean('is_active')->default(true);
            $table->text('description')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_items');
    }
};
