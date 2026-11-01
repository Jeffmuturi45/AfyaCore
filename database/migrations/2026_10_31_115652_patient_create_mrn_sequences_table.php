<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mrn_sequences', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('branch_id')->unique()->constrained('branches');
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->timestampTz('updated_at')->default(DB::raw('now()'));
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mrn_sequences');
    }
};
