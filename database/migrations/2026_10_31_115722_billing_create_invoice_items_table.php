<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->string('description', 255);
            $table->string('item_type', 30); // consultation|drug|optical|procedure|lab
            $table->foreignId('stock_item_id')->nullable()->constrained('stock_items');
            $table->integer('quantity')->default(1);
            $table->decimal('unit_price', 12, 2);
            $table->decimal('discount_percent', 5, 2)->default(0);
            $table->decimal('line_total', 12, 2);
            $table->decimal('insurance_covers', 12, 2)->default(0);
            $table->decimal('patient_pays', 12, 2);
            $table->timestampTz('created_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
    }
};
