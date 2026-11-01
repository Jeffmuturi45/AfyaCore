<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stock', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('stock_item_id')->constrained('stock_items');
            $table->integer('quantity_on_hand')->default(0);
            $table->timestampTz('last_counted_at')->nullable();
            $table->timestampTz('last_movement_at')->nullable();
            $table->unique(['branch_id', 'stock_item_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stock');
    }
};
