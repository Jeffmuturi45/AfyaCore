<?php

namespace App\Http\Requests\Consultation;

use Illuminate\Foundation\Http\FormRequest;

class CompleteConsultationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('consultation.complete') || $this->user()?->isSuperAdmin() || $this->user()?->isDoctor();
    }

    public function rules(): array
    {
        return [
            'presenting_complaints' => ['nullable', 'string'],
            'history_of_illness' => ['nullable', 'string'],
            'past_medical_history' => ['nullable', 'string'],
            'past_surgical_history' => ['nullable', 'string'],
            'family_history' => ['nullable', 'string'],
            'social_history' => ['nullable', 'string'],
            'drug_history' => ['nullable', 'string'],
            'examination_findings' => ['nullable', 'string'],
            'diagnosis' => ['required', 'string', 'min:3', 'max:500'],
            'diagnosis_codes' => ['nullable', 'array'],
            'diagnosis_codes.*' => ['string', 'max:20'],
            'treatment_plan' => ['nullable', 'string'],
            'follow_up_instructions' => ['nullable', 'string'],
        ];
    }
}
