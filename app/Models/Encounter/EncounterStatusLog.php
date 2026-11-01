<?php

namespace App\Models\Encounter;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class EncounterStatusLog extends Model
{
    public $timestamps = false;

    protected $table = 'encounter_status_log';

    protected $fillable = [
        'encounter_id',
        'from_status',
        'to_status',
        'changed_by',
        'reason',
        'changed_at',
    ];

    protected $casts = [
        'changed_at' => 'datetime',
    ];

    public function encounter()
    {
        return $this->belongsTo(Encounter::class);
    }

    public function changedBy()
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
