<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('outbox_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->uuid('uuid')->unique()->default(DB::raw('gen_random_uuid()'));
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('event_type', 100);
            $table->jsonb('payload');
            $table->smallInteger('schema_version')->default(1);
            $table->string('idempotency_key', 100)->unique();
            $table->string('status', 20)->default('pending');
            $table->smallInteger('attempt_count')->default(0);
            $table->timestampTz('last_attempted_at')->nullable();
            $table->timestampTz('next_retry_at')->nullable();
            $table->timestampTz('acknowledged_at')->nullable();
            $table->text('error_detail')->nullable();
            $table->timestampTz('created_at')->default(DB::raw('now()'));
            $table->index(['status', 'next_retry_at']);
            $table->index(['branch_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('outbox_events');
    }
};
