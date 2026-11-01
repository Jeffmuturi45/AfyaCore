<?php

namespace App\Policies;

use App\Models\Encounter\Encounter;
use App\Models\User;

class EncounterPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('encounter.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Encounter $encounter): bool
    {
        return ($user->can('encounter.view') || $user->isSuperAdmin()) && $user->belongsToBranch($encounter->branch_id);
    }

    public function create(User $user): bool
    {
        return $user->can('encounter.create') || $user->isSuperAdmin();
    }

    public function void(User $user, Encounter $encounter): bool
    {
        return ($user->can('encounter.void') || $user->isSuperAdmin()) && $user->belongsToBranch($encounter->branch_id);
    }

    public function triage(User $user, Encounter $encounter): bool
    {
        return ($user->can('triage.record') || $user->isSuperAdmin() || $user->isNurse()) && $user->belongsToBranch($encounter->branch_id);
    }

    public function consult(User $user, Encounter $encounter): bool
    {
        return ($user->can('consultation.document') || $user->isSuperAdmin() || $user->isDoctor()) && $user->belongsToBranch($encounter->branch_id);
    }
}

