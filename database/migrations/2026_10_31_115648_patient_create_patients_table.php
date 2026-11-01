<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('patients', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->string('global_patient_id', 20)->unique();
            $table->string('first_name', 100);
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100);
            $table->date('date_of_birth')->nullable();
            $table->string('gender', 10)->nullable();
            $table->string('national_id', 20)->nullable()->index();
            $table->string('passport_number', 30)->nullable();
            $table->string('phone_primary', 30)->nullable()->index();
            $table->string('phone_secondary', 30)->nullable();
            $table->string('email', 150)->nullable();
            $table->string('next_of_kin_name', 150)->nullable();
            $table->string('next_of_kin_phone', 30)->nullable();
            $table->string('next_of_kin_relation', 50)->nullable();
            $table->string('nationality', 50)->default('Kenyan');
            $table->string('county', 50)->nullable();
            $table->string('sub_county', 50)->nullable();
            $table->string('ward', 50)->nullable();
            $table->string('village', 100)->nullable();
            $table->boolean('is_merged')->default(false);
            $table->foreignId('merged_into')->nullable()->constrained('patients');
            $table->timestampTz('merged_at')->nullable();
            $table->foreignId('merged_by')->nullable()->constrained('users');
            $table->text('merge_reason')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
            $table->index(['last_name', 'first_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('patients');
    }
};
