<?php

namespace App\Models\Encounter;

use App\Models\Branch;
use App\Models\EyeClinic\EyeClinicEncounter;
use App\Models\Patient\Patient;
use App\Models\Pharmacy\Prescription;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Encounter extends Model
{
    protected $fillable = [
        'uuid', 'branch_id', 'patient_id', 'encounter_type', 'encounter_number',
        'status', 'assigned_to', 'created_by', 'voided_by', 'void_reason', 'void_at',
        'visit_date', 'arrival_time', 'completed_at', 'closed_at',
        'chief_complaint', 'is_referral_in', 'referral_source', 'notes',
    ];

    protected $casts = [
        'visit_date' => 'date',
        'arrival_time' => 'datetime',
        'completed_at' => 'datetime',
        'closed_at' => 'datetime',
        'void_at' => 'datetime',
        'is_referral_in' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($encounter) {
            if (empty($encounter->uuid)) {
                $encounter->uuid = (string) Str::uuid();
            }
        });
    }

    // Status constants
    const STATUS_REGISTERED = 'registered';

    const STATUS_TRIAGED = 'triaged';

    const STATUS_IN_QUEUE = 'in_queue';

    const STATUS_IN_PROGRESS = 'in_progress';

    const STATUS_CONSULTATION_COMPLETE = 'consultation_complete';

    const STATUS_PRE_AUTH_PENDING = 'pre_auth_pending';

    const STATUS_PRE_AUTH_APPROVED = 'pre_auth_approved';

    const STATUS_BILLED = 'billed';

    const STATUS_CLOSED = 'closed';

    const STATUS_VOID = 'void';

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function assignedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function voidedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voided_by');
    }

    public function statusLog(): HasMany
    {
        return $this->hasMany(EncounterStatusLog::class);
    }

    public function triageRecord(): HasOne
    {
        return $this->hasOne(TriageRecord::class);
    }

    public function clinicalNotes(): HasMany
    {
        return $this->hasMany(ClinicalNote::class);
    }

    public function currentNote()
    {
        return $this->clinicalNotes()->where('is_current', true)->first();
    }

    public function prescriptions(): HasMany
    {
        return $this->hasMany(Prescription::class);
    }

    public function referrals(): HasMany
    {
        return $this->hasMany(Referral::class);
    }

    public function latestReferral()
    {
        return $this->referrals()->latest('id')->first();
    }

    public function eyeClinicEncounter(): HasOne
    {
        return $this->hasOne(EyeClinicEncounter::class);
    }

    public function isOpd(): bool
    {
        return $this->encounter_type === 'opd';
    }

    public function isEyeClinic(): bool
    {
        return $this->encounter_type === 'eye_clinic';
    }

    public function isVoid(): bool
    {
        return $this->status === self::STATUS_VOID;
    }

    public function isClosed(): bool
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function isFinalized(): bool
    {
        return in_array($this->status, [self::STATUS_CLOSED, self::STATUS_VOID]);
    }
}
