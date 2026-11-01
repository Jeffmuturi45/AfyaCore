<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('branch_modules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->foreignId('branch_id')->constrained('branches');
            $table->string('module', 50); // opd|eye_clinic|pharmacy|optical_pos|insurance
            $table->boolean('is_active')->default(true);
            $table->jsonb('config')->default('{}');
            $table->timestampTz('activated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users');
            $table->unique(['branch_id', 'module']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('branch_modules');
    }
};
