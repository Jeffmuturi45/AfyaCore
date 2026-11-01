<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_merge_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('source_patient_id')->constrained('patients');
            $table->foreignId('target_patient_id')->constrained('patients');
            $table->foreignId('merged_by')->constrained('users');
            $table->text('reason');
            $table->jsonb('source_snapshot');
            $table->timestampTz('performed_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_merge_log');
    }
};
