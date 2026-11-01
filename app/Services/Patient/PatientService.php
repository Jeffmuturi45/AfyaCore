<?php
namespace App\Services\Patient;

use App\Models\Patient\Patient;
use App\Models\Patient\PatientBranchRecord;
use App\Services\Audit\AuditService;
use Illuminate\Support\Facades\DB;

class PatientService
{
    public static function register(array $data, int $branchId, string $branchCode): Patient
    {
        return DB::transaction(function () use ($data, $branchId, $branchCode) {
            $globalId = MrnService::generateGlobalId();
            $mrn      = MrnService::generate($branchId, $branchCode);

            $patient = Patient::create([
                'global_patient_id' => $globalId,
                'first_name'        => $data['first_name'],
                'middle_name'       => $data['middle_name'] ?? null,
                'last_name'         => $data['last_name'],
                'date_of_birth'     => $data['date_of_birth'] ?? null,
                'gender'            => $data['gender'] ?? null,
                'national_id'       => $data['national_id'] ?? null,
                'phone_primary'     => $data['phone_primary'] ?? null,
                'phone_secondary'   => $data['phone_secondary'] ?? null,
                'email'             => $data['email'] ?? null,
                'next_of_kin_name'  => $data['next_of_kin_name'] ?? null,
                'next_of_kin_phone' => $data['next_of_kin_phone'] ?? null,
                'next_of_kin_relation' => $data['next_of_kin_relation'] ?? null,
                'county'            => $data['county'] ?? null,
                'nationality'       => $data['nationality'] ?? 'Kenyan',
                'created_by'        => auth()->id(),
            ]);

            PatientBranchRecord::create([
                'patient_id'       => $patient->id,
                'branch_id'        => $branchId,
                'mrn'              => $mrn,
                'first_visit_date' => now()->toDateString(),
                'last_visit_date'  => now()->toDateString(),
                'visit_count'      => 1,
            ]);

            AuditService::log('patient.registered', $patient, [], [
                'global_patient_id' => $globalId,
                'mrn'               => $mrn,
                'branch_id'         => $branchId,
            ]);

            return $patient;
        });
    }

    public static function registerAtBranch(Patient $patient, int $branchId, string $branchCode): PatientBranchRecord
    {
        return DB::transaction(function () use ($patient, $branchId, $branchCode) {
            $existing = PatientBranchRecord::where('patient_id', $patient->id)
                ->where('branch_id', $branchId)->first();
            if ($existing) return $existing;

            $mrn = MrnService::generate($branchId, $branchCode);

            $record = PatientBranchRecord::create([
                'patient_id'       => $patient->id,
                'branch_id'        => $branchId,
                'mrn'              => $mrn,
                'first_visit_date' => now()->toDateString(),
                'last_visit_date'  => now()->toDateString(),
                'visit_count'      => 1,
            ]);

            AuditService::log('patient.registered_at_branch', $patient, [], [
                'mrn' => $mrn, 'branch_id' => $branchId,
            ]);

            return $record;
        });
    }
}