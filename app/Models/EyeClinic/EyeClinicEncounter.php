<?php

namespace App\Models\EyeClinic;

use App\Models\Encounter\Encounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EyeClinicEncounter extends Model
{
    protected $fillable = [
        'encounter_id', 'branch_id', 'optometrist_id',
        'chief_complaint', 'history', 'general_health_notes',
        'is_finalized', 'finalized_at',
    ];

    protected $casts = [
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function optometrist(): BelongsTo
    {
        return $this->belongsTo(User::class, 'optometrist_id');
    }

    public function visualAcuity(): HasMany
    {
        return $this->hasMany(VisualAcuityRecord::class, 'eye_encounter_id');
    }

    public function refractionRecords(): HasMany
    {
        return $this->hasMany(RefractionRecord::class, 'eye_encounter_id');
    }

    public function opticalPrescriptions(): HasMany
    {
        return $this->hasMany(OpticalPrescription::class, 'eye_encounter_id');
    }
}
