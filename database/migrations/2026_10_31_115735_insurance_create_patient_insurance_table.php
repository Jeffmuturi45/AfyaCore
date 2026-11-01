<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patient_insurance', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('patient_id')->constrained('patients');
            $table->foreignId('scheme_id')->constrained('insurance_schemes');
            $table->string('member_number', 50);
            $table->string('principal_name', 150)->nullable();
            $table->string('relationship', 30)->default('self');
            $table->date('valid_from')->nullable();
            $table->date('valid_to')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestampsTz();
            $table->unique(['patient_id', 'scheme_id', 'member_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patient_insurance');
    }
};
