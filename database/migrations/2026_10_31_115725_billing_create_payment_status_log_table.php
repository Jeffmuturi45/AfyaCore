<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_status_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('payment_id')->constrained('payments');
            $table->string('from_status', 20)->nullable();
            $table->string('to_status', 20);
            $table->string('actor', 50)->nullable();
            $table->text('reason')->nullable();
            $table->jsonb('payload_snapshot')->nullable();
            $table->timestampTz('changed_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_status_log');
    }
};
