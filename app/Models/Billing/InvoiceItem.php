<?php

namespace App\Models\Billing;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Inventory\StockItem;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id',
        'description',
        'item_type',
        'stock_item_id',
        'quantity',
        'unit_price',
        'discount_percent',
        'line_total',
        'insurance_covers',
        'patient_pays',
    ];

    protected $casts = [
        'quantity'         => 'integer',
        'unit_price'       => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'line_total'       => 'decimal:2',
        'insurance_covers' => 'decimal:2',
        'patient_pays'     => 'decimal:2',
    ];

    // ── Relationships ────────────────────────────────────────────

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }

    // ── Helpers ──────────────────────────────────────────────────

    public function itemTypeLabel(): string
    {
        return match($this->item_type) {
            'consultation' => 'Consultation',
            'drug'         => 'Drug / Pharmacy',
            'optical'      => 'Optical',
            'procedure'    => 'Procedure',
            'service'      => 'Service',
            default        => ucfirst($this->item_type),
        };
    }

    public function hasDiscount(): bool
    {
        return $this->discount_percent > 0;
    }
}