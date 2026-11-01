<?php
namespace App\Services\Patient;

use Illuminate\Support\Facades\DB;

class MrnService
{
    public static function generate(int $branchId, string $branchCode): string
    {
        return DB::transaction(function () use ($branchId, $branchCode) {
            $seq = DB::table('mrn_sequences')
                ->where('branch_id', $branchId)
                ->lockForUpdate()
                ->first();

            $next = $seq->last_sequence + 1;

            DB::table('mrn_sequences')
                ->where('branch_id', $branchId)
                ->update([
                    'last_sequence' => $next,
                    'updated_at'    => now(),
                ]);

            return $branchCode . '-' . str_pad($next, 6, '0', STR_PAD_LEFT);
        });
    }

    public static function generateGlobalId(): string
    {
        return DB::transaction(function () {
            // Use PostgreSQL native sequence — no table locking needed
            $result = DB::selectOne("SELECT nextval('global_patient_id_seq') AS next_id");
            return 'GP-' . str_pad($result->next_id, 6, '0', STR_PAD_LEFT);
        });
    }
}