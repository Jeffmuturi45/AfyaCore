<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->string('payment_reference', 50)->unique();
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('invoice_id')->constrained('invoices');
            $table->foreignId('patient_id')->constrained('patients');
            $table->string('payment_method', 20); // cash|mpesa|card|insurance|waiver
            $table->decimal('amount', 12, 2);
            $table->string('status', 20)->default('initiated');
            $table->string('paystack_reference', 100)->nullable()->index();
            $table->string('paystack_access_code', 100)->nullable();
            $table->jsonb('provider_response')->nullable();
            $table->string('idempotency_key', 100)->unique();
            $table->foreignId('received_by')->constrained('users');
            $table->timestampTz('confirmed_at')->nullable();
            $table->timestampTz('reversed_at')->nullable();
            $table->text('reversal_reason')->nullable();
            $table->foreignId('reversed_by')->nullable()->constrained('users');
            $table->timestampsTz();
            $table->index('invoice_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
