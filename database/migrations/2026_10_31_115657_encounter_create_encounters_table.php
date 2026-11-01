<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('encounters', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('encounter_type', 20); // opd|eye_clinic
            $table->string('encounter_number', 30)->unique();
            $table->string('status', 30)->default('registered');
            $table->foreignId('assigned_to')->nullable()->constrained('users');
            $table->foreignId('created_by')->constrained('users');
            $table->foreignId('voided_by')->nullable()->constrained('users');
            $table->text('void_reason')->nullable();
            $table->timestampTz('void_at')->nullable();
            $table->date('visit_date')->default(DB::raw('CURRENT_DATE'));
            $table->timestampTz('arrival_time')->default(DB::raw('now()'));
            $table->timestampTz('completed_at')->nullable();
            $table->timestampTz('closed_at')->nullable();
            $table->text('chief_complaint')->nullable();
            $table->boolean('is_referral_in')->default(false);
            $table->string('referral_source', 150)->nullable();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->index(['branch_id', 'visit_date']);
            $table->index('patient_id');
            $table->index('status');
            $table->index('assigned_to');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('encounters');
    }
};
