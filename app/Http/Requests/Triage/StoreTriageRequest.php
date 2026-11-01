<?php

namespace App\Http\Requests\Triage;

use Illuminate\Foundation\Http\FormRequest;

class StoreTriageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('triage.record') || $this->user()?->isSuperAdmin() || $this->user()?->isBranchAdmin() || $this->user()?->isNurse();
    }

    public function rules(): array
    {
        return [
            'temperature_c' => ['nullable', 'numeric', 'between:30.0,45.0'],
            'bp_systolic' => ['nullable', 'integer', 'between:40,260'],
            'bp_diastolic' => ['nullable', 'integer', 'between:20,180'],
            'pulse_bpm' => ['nullable', 'integer', 'between:30,240'],
            'spo2_percent' => ['nullable', 'numeric', 'between:50.0,100.0'],
            'weight_kg' => ['nullable', 'numeric', 'between:0.5,400.0'],
            'height_cm' => ['nullable', 'numeric', 'between:20.0,250.0'],
            'muac_cm' => ['nullable', 'numeric', 'between:5.0,50.0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'assign_to_doctor_id' => ['nullable', 'exists:users,id'],
        ];
    }
}
