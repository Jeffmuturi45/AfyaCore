<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_duplicate_flags', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('patient_a_id')->constrained('patients');
            $table->foreignId('patient_b_id')->constrained('patients');
            $table->decimal('similarity_score', 5, 4)->nullable();
            $table->jsonb('flagged_fields')->nullable();
            $table->string('status', 20)->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestampTz('reviewed_at')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->unique(['patient_a_id', 'patient_b_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_duplicate_flags');
    }
};
