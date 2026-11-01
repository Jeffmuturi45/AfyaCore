<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\User;
use App\Models\Branch;
use App\Models\Patient\Patient;

class Payment extends Model
{
    protected $fillable = [
        'payment_reference',
        'idempotency_key',
        'branch_id',
        'invoice_id',
        'patient_id',
        'payment_method',   // cash | mpesa | card | insurance | waiver
        'amount',
        'status',           // initiated | confirmed | failed | reversed
        'reference',        // M-Pesa code, card auth, etc.
        'notes',
        'received_by',
        'confirmed_at',
        'paid_at',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'paid_at'      => 'datetime',
        'confirmed_at' => 'datetime',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function receivedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function statusLogs(): HasMany
    {
        return $this->hasMany(PaymentStatusLog::class)->orderBy('changed_at');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function methodLabel(): string
    {
        return match($this->payment_method) {
            'cash'      => 'Cash',
            'mpesa'     => 'M-Pesa',
            'card'      => 'Card',
            'insurance' => 'Insurance',
            'waiver'    => 'Waiver',
            default     => ucfirst($this->payment_method),
        };
    }

    public function methodIcon(): string
    {
        return match($this->payment_method) {
            'cash'      => 'bi-cash-coin',
            'mpesa'     => 'bi-phone',
            'card'      => 'bi-credit-card',
            'insurance' => 'bi-shield-check',
            'waiver'    => 'bi-gift',
            default     => 'bi-cash',
        };
    }

    public function statusBadge(): string
    {
        return match($this->status) {
            'confirmed' => 'success',
            'initiated' => 'warning',
            'failed'    => 'danger',
            'reversed'  => 'secondary',
            default     => 'light',
        };
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'confirmed' => 'Confirmed',
            'initiated' => 'Pending',
            'failed'    => 'Failed',
            'reversed'  => 'Reversed',
            default     => ucfirst($this->status),
        };
    }
}