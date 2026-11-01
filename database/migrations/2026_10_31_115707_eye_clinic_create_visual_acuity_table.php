<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visual_acuity_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('eye_encounter_id')->constrained('eye_clinic_encounters');
            $table->string('eye', 5); // RE|LE|BE
            $table->string('unaided_distance', 20)->nullable();
            $table->string('unaided_near', 20)->nullable();
            $table->string('aided_distance', 20)->nullable();
            $table->string('aided_near', 20)->nullable();
            $table->string('pinhole', 20)->nullable();
            $table->foreignId('recorded_by')->constrained('users');
            $table->timestampTz('recorded_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visual_acuity_records');
    }
};
