<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('prescription_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('prescription_id')->constrained('prescriptions');
            $table->foreignId('stock_item_id')->constrained('stock_items');
            $table->string('drug_name', 200);
            $table->string('dose', 50);
            $table->string('route', 50)->nullable();
            $table->string('frequency', 50);
            $table->string('duration', 50)->nullable();
            $table->integer('quantity_prescribed');
            $table->integer('quantity_dispensed')->default(0);
            $table->text('instructions')->nullable();
            $table->string('status', 20)->default('pending');
            $table->timestampTz('created_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prescription_items');
    }
};
