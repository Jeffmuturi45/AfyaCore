<?php

namespace App\Models\Encounter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class TriageRecord extends Model
{
    protected $fillable = [
        'encounter_id', 'branch_id', 'temperature_c', 'bp_systolic', 'bp_diastolic',
        'pulse_bpm', 'spo2_percent', 'weight_kg', 'height_cm', 'bmi', 'muac_cm',
        'recorded_by', 'recorded_at', 'notes',
    ];

    protected $casts = ['recorded_at' => 'datetime'];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function recordedBy()
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function getBpAttribute(): ?string
    {
        if ($this->bp_systolic && $this->bp_diastolic) {
            return "{$this->bp_systolic}/{$this->bp_diastolic} mmHg";
        }

        return null;
    }
}
