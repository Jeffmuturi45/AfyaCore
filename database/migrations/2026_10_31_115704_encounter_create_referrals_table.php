<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('referrals', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('encounter_id')->constrained('encounters');
            $table->foreignId('branch_id')->constrained('branches');
            $table->foreignId('referred_by')->constrained('users');
            $table->string('destination', 200);
            $table->string('urgency', 20)->default('routine');
            $table->text('reason');
            $table->text('clinical_summary')->nullable();
            $table->string('status', 20)->default('pending');
            $table->boolean('follow_up_required')->default(false);
            $table->text('follow_up_notes')->nullable();
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');
    }
};
