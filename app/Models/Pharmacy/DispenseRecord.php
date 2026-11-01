<?php

namespace App\Models\Pharmacy;

use App\Models\Branch;
use App\Models\Encounter\Encounter;
use App\Models\Inventory\StockItem;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DispenseRecord extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'prescription_item_id',
        'encounter_id',
        'branch_id',
        'stock_item_id',
        'dispensed_by',
        'quantity_dispensed',
        'batch_number',
        'expiry_date',
        'dispensed_at',
        'notes',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'dispensed_at' => 'datetime',
        'quantity_dispensed' => 'integer',
    ];

    public function prescriptionItem(): BelongsTo
    {
        return $this->belongsTo(PrescriptionItem::class);
    }

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class);
    }

    public function dispensedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dispensed_by');
    }
}
