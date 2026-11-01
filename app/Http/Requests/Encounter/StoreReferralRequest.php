<?php

namespace App\Http\Requests\Encounter;

use Illuminate\Foundation\Http\FormRequest;

class StoreReferralRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('referral.create') || $this->user()?->isSuperAdmin() || $this->user()?->isDoctor() || $this->user()?->isOptometrist();
    }

    public function rules(): array
    {
        return [
            'destination' => ['required', 'string', 'max:200'],
            'urgency' => ['required', 'string', 'in:routine,urgent,emergency'],
            'reason' => ['required', 'string', 'min:5', 'max:2000'],
            'clinical_summary' => ['nullable', 'string', 'max:2000'],
            'follow_up_required' => ['nullable', 'boolean'],
            'follow_up_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
