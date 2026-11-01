<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dispense_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('prescription_item_id')->constrained('prescription_items');
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('stock_item_id')->constrained('stock_items');
            $table->foreignId('dispensed_by')->constrained('users');
            $table->integer('quantity_dispensed');
            $table->string('batch_number', 50)->nullable();
            $table->date('expiry_date')->nullable();
            $table->timestampTz('dispensed_at')->default(DB::raw('now()'));
            $table->text('notes')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dispense_records');
    }
};
