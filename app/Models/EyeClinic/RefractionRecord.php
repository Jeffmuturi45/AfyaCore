<?php

namespace App\Models\EyeClinic;

use Illuminate\Database\Eloquent\Model;

class RefractionRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'eye_encounter_id', 'eye', 'sphere', 'cylinder', 'axis', 'addition',
        'prism', 'base', 'va_with_correction', 'is_final', 'recorded_by', 'recorded_at',
    ];

    protected $casts = ['is_final' => 'boolean', 'recorded_at' => 'datetime'];
}
