<?php

// Option B — use this ONLY if you already ran the previous migration
// and the table exists with column name "quantity".
// Run: php artisan migrate

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('branch_stocks', function (Blueprint $table) {
            $table->renameColumn('quantity', 'quantity_on_hand');
        });
    }

    public function down(): void
    {
        Schema::table('branch_stocks', function (Blueprint $table) {
            $table->renameColumn('quantity_on_hand', 'quantity');
        });
    }
};