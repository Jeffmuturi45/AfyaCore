<?php

namespace App\Services\Audit;

use App\Models\AuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

class AuditService
{
    public static function log(
        string $action,
        ?object $model = null,
        array $oldValues = [],
        array $newValues = [],
        string $outcome = 'success',
        ?string $notes = null
    ): void {
        $user = Auth::user();

        AuditLog::create([
            'branch_id' => $user?->branch_id,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'user_role' => $user?->role,
            'action' => $action,
            'model_type' => $model ? get_class($model) : null,
            'model_id' => $model?->id,
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'session_id' => session()->getId(),
            'outcome' => $outcome,
            'notes' => $notes,
            'occurred_at' => now(),
        ]);
    }
}
