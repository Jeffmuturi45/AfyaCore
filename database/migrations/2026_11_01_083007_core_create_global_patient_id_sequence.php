<?php
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void {
        DB::statement("CREATE SEQUENCE IF NOT EXISTS global_patient_id_seq START 1 INCREMENT 1");
    }
    public function down(): void {
        DB::statement("DROP SEQUENCE IF EXISTS global_patient_id_seq");
    }
};