<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false;

    public $incrementing = true;

    protected $fillable = [
        'branch_id', 'user_id', 'user_name', 'user_role', 'action',
        'model_type', 'model_id', 'old_values', 'new_values',
        'ip_address', 'user_agent', 'session_id', 'outcome', 'notes', 'occurred_at',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'occurred_at' => 'datetime',
    ];
}
