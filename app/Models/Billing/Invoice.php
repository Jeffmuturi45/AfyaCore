<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Patient\Patient;
use App\Models\Encounter\Encounter;
use App\Models\Branch;
use App\Models\User;

class Invoice extends Model
{
    protected $fillable = [
        'uuid',
        'invoice_number',
        'branch_id',
        'encounter_id',
        'patient_id',
        'status',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total_amount',
        'amount_paid',
        'balance_due',
        'insurance_amount',
        'patient_amount',
        'notes',
        'created_by',
        'issued_at',
        'paid_at',
    ];

    protected $casts = [
        'subtotal'         => 'decimal:2',
        'discount_amount'  => 'decimal:2',
        'tax_amount'       => 'decimal:2',
        'total_amount'     => 'decimal:2',
        'amount_paid'      => 'decimal:2',
        'balance_due'      => 'decimal:2',
        'insurance_amount' => 'decimal:2',
        'patient_amount'   => 'decimal:2',
        'issued_at'        => 'datetime',
        'paid_at'          => 'datetime',
    ];

    // Use integer ID as route key (not UUID) so /billing/2 works
    public function getRouteKeyName(): string
    {
        return 'id';
    }

    // ── Relationships ────────────────────────────────────────────

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Scopes ───────────────────────────────────────────────────

    public function scopeForBranch($query, int $branchId)
    {
        return $query->where('branch_id', $branchId);
    }

    public function scopeOutstanding($query)
    {
        return $query->where('balance_due', '>', 0)
                     ->whereNotIn('status', ['reversed', 'cancelled']);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function isPaid(): bool
    {
        return $this->balance_due <= 0;
    }

    public function isPartiallyPaid(): bool
    {
        return $this->amount_paid > 0 && $this->balance_due > 0;
    }

    public function isReversed(): bool
    {
        return $this->status === 'reversed';
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'issued'   => 'Issued',
            'paid'     => 'Paid',
            'partial'  => 'Partial',
            'reversed' => 'Reversed',
            'cancelled'=> 'Cancelled',
            default    => ucfirst($this->status),
        };
    }

    public function statusColor(): string
    {
        return match($this->status) {
            'paid'     => 'green',
            'partial'  => 'amber',
            'reversed',
            'cancelled'=> 'red',
            default    => 'blue',
        };
    }
}