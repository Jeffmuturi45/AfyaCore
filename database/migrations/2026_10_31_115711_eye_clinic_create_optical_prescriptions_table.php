<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('optical_prescriptions', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('eye_encounter_id')->constrained('eye_clinic_encounters');
            $table->foreignId('prescribed_by')->constrained('users');
            $table->string('prescription_type', 20); // distance|near|bifocal|progressive|safety
            $table->decimal('re_sphere', 5, 2)->nullable();
            $table->decimal('re_cylinder', 5, 2)->nullable();
            $table->smallInteger('re_axis')->nullable();
            $table->decimal('re_addition', 4, 2)->nullable();
            $table->decimal('le_sphere', 5, 2)->nullable();
            $table->decimal('le_cylinder', 5, 2)->nullable();
            $table->smallInteger('le_axis')->nullable();
            $table->decimal('le_addition', 4, 2)->nullable();
            $table->decimal('pd_distance', 4, 1)->nullable();
            $table->decimal('pd_near', 4, 1)->nullable();
            $table->text('frame_recommendation')->nullable();
            $table->text('lens_recommendation')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_dispensed')->default(false);
            $table->timestampTz('dispensed_at')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('optical_prescriptions');
    }
};
