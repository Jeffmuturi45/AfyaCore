<?php

namespace App\Http\Requests\Encounter;

use Illuminate\Foundation\Http\FormRequest;

class StoreEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('encounter.create') || $this->user()?->isSuperAdmin() || $this->user()?->isBranchAdmin() || $this->user()?->isReceptionist();
    }

    public function rules(): array
    {
        return [
            'patient_id' => ['required', 'exists:patients,id'],
            'encounter_type' => ['nullable', 'string', 'in:opd,eye_clinic'],
            'assigned_to' => ['nullable', 'exists:users,id'],
            'visit_date' => ['nullable', 'date'],
            'chief_complaint' => ['nullable', 'string', 'max:1000'],
            'is_referral_in' => ['nullable', 'boolean'],
            'referral_source' => ['nullable', 'string', 'max:150'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
