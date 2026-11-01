<?php

namespace App\Http\Requests\Encounter;

use Illuminate\Foundation\Http\FormRequest;

class VoidEncounterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('encounter.void') || $this->user()?->isSuperAdmin() || $this->user()?->isBranchAdmin();
    }

    public function rules(): array
    {
        return [
            'void_reason' => ['required', 'string', 'min:5', 'max:500'],
        ];
    }
}

