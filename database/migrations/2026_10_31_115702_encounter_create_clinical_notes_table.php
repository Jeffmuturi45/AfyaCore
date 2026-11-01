<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clinical_notes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->smallInteger('version')->default(1);
            $table->boolean('is_current')->default(true);
            $table->text('presenting_complaints')->nullable();
            $table->text('history_of_illness')->nullable();
            $table->text('past_medical_history')->nullable();
            $table->text('past_surgical_history')->nullable();
            $table->text('family_history')->nullable();
            $table->text('social_history')->nullable();
            $table->text('drug_history')->nullable();
            $table->text('examination_findings')->nullable();
            $table->text('diagnosis')->nullable();
            $table->jsonb('diagnosis_codes')->nullable();
            $table->text('treatment_plan')->nullable();
            $table->text('follow_up_instructions')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestampTz('finalized_at')->nullable();
            $table->foreignId('authored_by')->constrained('users');
            $table->foreignId('amended_by')->nullable()->constrained('users');
            $table->text('amendment_reason')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clinical_notes');
    }
};
