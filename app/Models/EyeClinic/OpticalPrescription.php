<?php

namespace App\Models\EyeClinic;

use Illuminate\Database\Eloquent\Model;

class OpticalPrescription extends Model
{
    protected $fillable = [
        'encounter_id', 'eye_encounter_id', 'prescribed_by', 'prescription_type',
        're_sphere', 're_cylinder', 're_axis', 're_addition',
        'le_sphere', 'le_cylinder', 'le_axis', 'le_addition',
        'pd_distance', 'pd_near', 'frame_recommendation', 'lens_recommendation',
        'notes', 'is_dispensed', 'dispensed_at',
    ];

    protected $casts = [
        'is_dispensed' => 'boolean',
        'dispensed_at' => 'datetime',
    ];
}
