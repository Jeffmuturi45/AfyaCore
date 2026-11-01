<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_claims', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('scheme_id')->constrained('insurance_schemes');
            $table->foreignId('patient_insurance_id')->constrained('patient_insurance');
            $table->string('claim_number', 50)->nullable();
            $table->string('preauth_number', 50)->nullable();
            $table->string('status', 30)->default('draft');
            $table->decimal('amount_claimed', 12, 2);
            $table->decimal('amount_approved', 12, 2)->nullable();
            $table->decimal('amount_paid', 12, 2)->nullable();
            $table->timestampTz('submitted_at')->nullable();
            $table->timestampTz('approved_at')->nullable();
            $table->timestampTz('paid_at')->nullable();
            $table->jsonb('provider_response')->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->constrained('users');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_claims');
    }
};
