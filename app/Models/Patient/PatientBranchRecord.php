<?php

namespace App\Models\Patient;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;

class PatientBranchRecord extends Model
{
    protected $fillable = [
        'patient_id', 'branch_id', 'mrn',
        'first_visit_date', 'last_visit_date', 'visit_count', 'notes',
    ];

    protected $casts = [
        'first_visit_date' => 'date',
        'last_visit_date' => 'date',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }
}
