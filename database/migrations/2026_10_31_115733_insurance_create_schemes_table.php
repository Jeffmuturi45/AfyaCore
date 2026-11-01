<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('insurance_schemes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 150);
            $table->string('code', 20)->unique();
            $table->string('api_endpoint', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_preauth')->default(false);
            $table->jsonb('config')->default('{}');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('insurance_schemes');
    }
};
