<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_branch_records', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('mrn', 20)->unique()->index();
            $table->date('first_visit_date');
            $table->date('last_visit_date')->nullable();
            $table->integer('visit_count')->default(1);
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->unique(['patient_id', 'branch_id']);
            $table->index(['branch_id', 'patient_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_branch_records');
    }
};
