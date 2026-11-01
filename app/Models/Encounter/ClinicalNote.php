<?php

namespace App\Models\Encounter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class ClinicalNote extends Model
{
    protected $fillable = [
        'encounter_id', 'version', 'is_current',
        'presenting_complaints', 'history_of_illness', 'past_medical_history',
        'past_surgical_history', 'family_history', 'social_history', 'drug_history',
        'examination_findings', 'diagnosis', 'diagnosis_codes', 'treatment_plan',
        'follow_up_instructions', 'is_finalized', 'finalized_at',
        'authored_by', 'amended_by', 'amendment_reason',
    ];

    protected $casts = [
        'is_current' => 'boolean',
        'is_finalized' => 'boolean',
        'finalized_at' => 'datetime',
        'diagnosis_codes' => 'array',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function authoredBy()
    {
        return $this->belongsTo(User::class, 'authored_by');
    }

    public function amendedBy()
    {
        return $this->belongsTo(User::class, 'amended_by');
    }
}
