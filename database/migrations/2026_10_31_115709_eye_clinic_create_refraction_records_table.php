<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('refraction_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('eye_encounter_id')->constrained('eye_clinic_encounters');
            $table->string('eye', 5); // RE|LE
            $table->decimal('sphere', 5, 2)->nullable();
            $table->decimal('cylinder', 5, 2)->nullable();
            $table->smallInteger('axis')->nullable();
            $table->decimal('addition', 4, 2)->nullable();
            $table->decimal('prism', 4, 2)->nullable();
            $table->string('base', 10)->nullable();
            $table->string('va_with_correction', 20)->nullable();
            $table->boolean('is_final')->default(false);
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestampTz('recorded_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('refraction_records');
    }
};
