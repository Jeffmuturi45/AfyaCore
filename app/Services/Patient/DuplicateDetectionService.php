<?php
namespace App\Services\Patient;

use App\Models\Patient\Patient;

class DuplicateDetectionService
{
    public static function findPotentialDuplicates(array $data): \Illuminate\Support\Collection
    {
        return Patient::where('is_merged', false)
            ->where(function ($q) use ($data) {
                if (!empty($data['national_id'])) {
                    $q->orWhere('national_id', $data['national_id']);
                }
                if (!empty($data['phone_primary'])) {
                    $q->orWhere('phone_primary', $data['phone_primary']);
                }
                if (!empty($data['first_name']) && !empty($data['last_name'])) {
                    $q->orWhere(function ($q2) use ($data) {
                        $q2->whereRaw('LOWER(first_name) = ?', [strtolower($data['first_name'])])
                           ->whereRaw('LOWER(last_name) = ?', [strtolower($data['last_name'])]);
                        if (!empty($data['date_of_birth'])) {
                            $q2->where('date_of_birth', $data['date_of_birth']);
                        }
                    });
                }
            })
            ->with('branchRecords.branch')
            ->limit(5)
            ->get();
    }
}