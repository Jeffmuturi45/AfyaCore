<?php

namespace App\Models\Patient;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MrnSequence extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id',
        'last_sequence',
        'updated_at',
    ];

    protected $casts = [
        'last_sequence' => 'integer',
        'updated_at' => 'datetime',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }
}
