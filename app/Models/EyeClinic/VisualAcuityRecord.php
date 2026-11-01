<?php

namespace App\Models\EyeClinic;

use Illuminate\Database\Eloquent\Model;

class VisualAcuityRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'eye_encounter_id', 'eye', 'unaided_distance', 'unaided_near',
        'aided_distance', 'aided_near', 'pinhole', 'recorded_by', 'recorded_at',
    ];

    protected $casts = ['recorded_at' => 'datetime'];
}
