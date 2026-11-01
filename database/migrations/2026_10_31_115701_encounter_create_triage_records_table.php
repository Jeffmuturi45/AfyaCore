<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('triage_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('encounter_id')->unique()->constrained('encounters');
            $table->foreignId('branch_id')->constrained('branches');
            $table->decimal('temperature_c', 4, 1)->nullable();
            $table->smallInteger('bp_systolic')->nullable();
            $table->smallInteger('bp_diastolic')->nullable();
            $table->smallInteger('pulse_bpm')->nullable();
            $table->decimal('spo2_percent', 4, 1)->nullable();
            $table->decimal('weight_kg', 5, 2)->nullable();
            $table->decimal('height_cm', 5, 1)->nullable();
            $table->decimal('bmi', 4, 1)->nullable();
            $table->decimal('muac_cm', 4, 1)->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestampTz('recorded_at')->default(DB::raw('now()'));
            $table->text('notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('triage_records');
    }
};
