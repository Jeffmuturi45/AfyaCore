<?php

namespace App\Models\Patient;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PatientDuplicateFlag extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'patient_a_id',
        'patient_b_id',
        'similarity_score',
        'flagged_fields',
        'status',
        'reviewed_by',
        'reviewed_at',
        'created_at',
    ];

    protected $casts = [
        'similarity_score' => 'decimal:4',
        'flagged_fields' => 'array',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
    ];

    public function patientA(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_a_id');
    }

    public function patientB(): BelongsTo
    {
        return $this->belongsTo(Patient::class, 'patient_b_id');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
