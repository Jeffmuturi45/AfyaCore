<?php

namespace App\Models\Patient;

use App\Models\Encounter\Encounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Patient extends Model
{
    protected $fillable = [
        'uuid',
        'global_patient_id',
        'first_name',
        'middle_name',
        'last_name',
        'date_of_birth',
        'gender',
        'national_id',
        'passport_number',
        'phone_primary',
        'phone_secondary',
        'email',
        'next_of_kin_name',
        'next_of_kin_phone',
        'next_of_kin_relation',
        'nationality',
        'county',
        'sub_county',
        'ward',
        'village',
        'is_merged',
        'merged_into',
        'merged_at',
        'merged_by',
        'merge_reason',
        'created_by',
    ];

    protected $casts = [
        'date_of_birth' => 'date',
        'is_merged' => 'boolean',
        'merged_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($patient) {
            if (empty($patient->uuid)) {
                $patient->uuid = (string) Str::uuid();
            }
        });
    }

    public function branchRecords(): HasMany
    {
        return $this->hasMany(PatientBranchRecord::class);
    }

    public function branchRecord(int $branchId)
    {
        return $this->branchRecords()->where('branch_id', $branchId)->first();
    }

    public function mrn(?int $branchId = null): ?string
    {
        if ($branchId) {
            return $this->branchRecord($branchId)?->mrn;
        }

        return $this->branchRecords()->first()?->mrn;
    }

    public function encounters(): HasMany
    {
        return $this->hasMany(Encounter::class);
    }

    public function duplicateFlags(): HasMany
    {
        return $this->hasMany(PatientDuplicateFlag::class, 'patient_a_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function mergedInto(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'merged_into');
    }

    public function mergedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'merged_by');
    }

    public function getNameAttribute(): string
{
    return trim("{$this->first_name} {$this->middle_name} {$this->last_name}");
}

public function getInitialsAttribute(): string
{
    return strtoupper(substr($this->first_name, 0, 1) . substr($this->last_name, 0, 1));
}

    public function getAgeAttribute(): ?string
    {
        if (! $this->date_of_birth) {
            return null;
        }

        return (int) $this->date_of_birth->diffInYears(now()).' yrs';
    }
}
