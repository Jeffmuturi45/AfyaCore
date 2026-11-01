<?php
namespace App\Services\Encounter;

use App\Models\Encounter\Encounter;
use App\Models\Encounter\EncounterStatusLog;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class EncounterService
{
    public static function create(array $data, int $branchId): Encounter
    {
        return DB::transaction(function () use ($data, $branchId) {
            $branch = auth()->user()->branch;
            $type   = $branch->isEyeClinic() ? 'eye_clinic' : 'opd';
            $number = self::generateNumber($branch->code, $type);

            $encounter = Encounter::create([
                'branch_id'       => $branchId,
                'patient_id'      => $data['patient_id'],
                'encounter_type'  => $type,
                'encounter_number'=> $number,
                'status'          => 'registered',
                'chief_complaint' => $data['chief_complaint'] ?? null,
                'is_referral_in'  => $data['is_referral_in'] ?? false,
                'referral_source' => $data['referral_source'] ?? null,
                'visit_date'      => now()->toDateString(),
                'created_by'      => auth()->id(),
            ]);

            EncounterStatusLog::create([
                'encounter_id' => $encounter->id,
                'from_status'  => null,
                'to_status'    => 'registered',
                'changed_by'   => auth()->id(),
                'changed_at'   => now(),
            ]);

            // Eye clinic: skip triage, go straight to in_progress
            if ($type === 'eye_clinic') {
                self::transition($encounter, 'in_progress', auth()->user());
            }

            AuditService::log('encounter.created', $encounter);

            return $encounter;
        });
    }

    public static function transition(Encounter $encounter, string $toStatus, $user, ?string $reason = null): void
    {
        $from = $encounter->status;
        $encounter->update(['status' => $toStatus]);
        EncounterStatusLog::create([
            'encounter_id' => $encounter->id,
            'from_status'  => $from,
            'to_status'    => $toStatus,
            'changed_by'   => $user->id,
            'reason'       => $reason,
            'changed_at'   => now(),
        ]);
        AuditService::log('encounter.status_changed', $encounter, ['status' => $from], ['status' => $toStatus]);
    }

    private static function generateNumber(string $code, string $type): string
    {
        $prefix = $code . '-' . ($type === 'eye_clinic' ? 'EYE' : 'OPD') . '-' . now()->format('Ymd');
        $count  = Encounter::where('encounter_number', 'LIKE', $prefix . '%')->count();
        return $prefix . '-' . str_pad($count + 1, 4, '0', STR_PAD_LEFT);
    }
}