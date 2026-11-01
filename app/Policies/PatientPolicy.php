<?php

namespace App\Policies;

use App\Models\Patient\Patient;
use App\Models\User;

class PatientPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('patient.view') || $user->isSuperAdmin();
    }

    public function view(User $user, Patient $patient): bool
    {
        return $user->can('patient.view') || $user->isSuperAdmin();
    }

    public function create(User $user): bool
    {
        return $user->can('patient.register') || $user->isSuperAdmin();
    }

    public function update(User $user, Patient $patient): bool
    {
        return $user->can('patient.edit') || $user->isSuperAdmin();
    }

    public function merge(User $user, Patient $patient): bool
    {
        return $user->can('patient.merge') || $user->isSuperAdmin();
    }
}

