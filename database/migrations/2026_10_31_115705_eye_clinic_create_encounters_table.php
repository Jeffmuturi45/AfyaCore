<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eye_clinic_encounters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('encounter_id')->unique()->constrained('encounters');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('optometrist_id')->constrained('users');
            $table->text('chief_complaint')->nullable();
            $table->text('history')->nullable();
            $table->text('general_health_notes')->nullable();
            $table->boolean('is_finalized')->default(false);
            $table->timestampTz('finalized_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('eye_clinic_encounters');
    }
};
