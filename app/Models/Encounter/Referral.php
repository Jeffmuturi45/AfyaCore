<?php

namespace App\Models\Encounter;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Referral extends Model
{
    protected $fillable = [
        'encounter_id',
        'branch_id',
        'referred_by',
        'destination',
        'urgency',
        'reason',
        'clinical_summary',
        'status',
        'follow_up_required',
        'follow_up_notes',
    ];

    protected $casts = [
        'follow_up_required' => 'boolean',
    ];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function referredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referred_by');
    }
}

