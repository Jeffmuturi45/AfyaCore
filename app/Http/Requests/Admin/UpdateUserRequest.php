<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.manage') || $this->user()?->isSuperAdmin() || $this->user()?->isBranchAdmin();
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');
        $roles = array_keys(config('hmis.roles', []));

        return [
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:150', Rule::unique('users', 'email')->ignore($userId)],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'branch_id'             => ['required', 'exists:branches,id'],
            'role'                  => ['required', 'string', 'in:' . implode(',', $roles)],
            'password'              => ['nullable', 'string', 'min:8', 'max:100'],
            'force_password_change' => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
        ];
    }
}

