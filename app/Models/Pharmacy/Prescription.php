<?php

namespace App\Models\Pharmacy;

use App\Models\Encounter\Encounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Prescription extends Model
{
    protected $fillable = [
        'uuid',
        'encounter_id',
        'prescribed_by',
        'status',
        'notes',
    ];

    protected static function booted(): void
    {
        static::creating(function ($prescription) {
            if (empty($prescription->uuid)) {
                $prescription->uuid = (string) Str::uuid();
            }
        });
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function prescribedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prescribed_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}

