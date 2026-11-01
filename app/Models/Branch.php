<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Branch extends Model
{
    protected $fillable = [
        'code', 'name', 'branch_type', 'address', 'phone', 'email', 'is_active', 'settings',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'settings' => 'array',
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(BranchModule::class);
    }

    public function hasModule(string $module): bool
    {
        return $this->modules()
            ->where('module', $module)
            ->where('is_active', true)
            ->exists();
    }

    public function isEyeClinic(): bool
    {
        return $this->branch_type === 'eye_clinic';
    }

    public function isOpd(): bool
    {
        return $this->branch_type === 'opd';
    }
}
