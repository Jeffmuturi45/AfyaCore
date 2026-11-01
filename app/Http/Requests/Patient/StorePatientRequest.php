<?php
namespace App\Http\Requests\Patient;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'first_name'           => ['required', 'string', 'max:100'],
            'middle_name'          => ['nullable', 'string', 'max:100'],
            'last_name'            => ['required', 'string', 'max:100'],
            'date_of_birth'        => ['nullable', 'date', 'before:today'],
            'gender'               => ['nullable', 'in:male,female,other,unknown'],
            'national_id'          => ['nullable', 'string', 'max:20'],
            'phone_primary'        => ['nullable', 'string', 'max:30'],
            'phone_secondary'      => ['nullable', 'string', 'max:30'],
            'email'                => ['nullable', 'email', 'max:150'],
            'nationality'          => ['nullable', 'string', 'max:50'],
            'county'               => ['nullable', 'string', 'max:50'],
            'next_of_kin_name'     => ['nullable', 'string', 'max:150'],
            'next_of_kin_phone'    => ['nullable', 'string', 'max:30'],
            'next_of_kin_relation' => ['nullable', 'string', 'max:50'],
            'confirm_duplicate'    => ['nullable', 'boolean'],
        ];
    }
}