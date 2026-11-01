<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected $fillable = [
        'branch_id', 'name', 'email', 'phone', 'password', 'role',
        'is_active', 'force_password_change', 'mfa_enabled', 'created_by',
        'failed_login_count', 'last_login_at', 'last_login_ip', 'locked_until',
    ];

    protected $hidden = ['password', 'remember_token', 'mfa_secret'];

    protected $casts = [
        'is_active' => 'boolean',
        'force_password_change' => 'boolean',
        'mfa_enabled' => 'boolean',
        'last_login_at' => 'datetime',
        'locked_until' => 'datetime',
    ];

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function isLockedOut(): bool
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    public function isBranchAdmin(): bool
    {
        return $this->role === 'branch_admin';
    }

    public function isNurse(): bool
    {
        return $this->role === 'nurse';
    }

    public function isDoctor(): bool
    {
        return in_array($this->role, ['doctor', 'branch_admin']);
    }

    public function isOptometrist(): bool
    {
        return $this->role === 'optometrist';
    }

    public function isReceptionist(): bool
    {
        return $this->role === 'receptionist';
    }

    public function isPharmacist(): bool
    {
        return $this->role === 'pharmacist';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }

    public function belongsToBranch(int $branchId): bool
    {
        return $this->isSuperAdmin() || $this->branch_id === $branchId;
    }

    public function encounters()
{
    return $this->hasMany(\App\Models\Encounter\Encounter::class, 'assigned_to');
}
}
