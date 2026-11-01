<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('user.manage') || $this->user()?->isSuperAdmin() || $this->user()?->isBranchAdmin();
    }

    public function rules(): array
    {
        $roles = array_keys(config('hmis.roles', []));

        return [
            'name'                  => ['required', 'string', 'max:100'],
            'email'                 => ['required', 'email', 'max:150', 'unique:users,email'],
            'phone'                 => ['nullable', 'string', 'max:30'],
            'branch_id'             => ['required', 'exists:branches,id'],
            'role'                  => ['required', 'string', 'in:' . implode(',', $roles)],
            'password'              => ['required', 'string', 'min:8', 'max:100'],
            'force_password_change' => ['nullable', 'boolean'],
            'is_active'             => ['nullable', 'boolean'],
        ];
    }
}

