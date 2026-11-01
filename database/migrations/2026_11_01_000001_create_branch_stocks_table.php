<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')
                  ->constrained('branches')
                  ->cascadeOnDelete();
            $table->foreignId('stock_item_id')
                  ->constrained('stock_items')
                  ->cascadeOnDelete();
            $table->integer('quantity')->default(0);
            $table->integer('reorder_level')->default(0);
            $table->integer('max_level')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Each stock item appears once per branch
            $table->unique(['branch_id', 'stock_item_id']);

            $table->index('branch_id');
            $table->index('stock_item_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_stocks');
    }
};