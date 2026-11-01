<?php

namespace App\Models\Pharmacy;

use App\Models\Inventory\StockItem;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PrescriptionItem extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'prescription_id',
        'stock_item_id',
        'drug_name',
        'dose',
        'route',
        'frequency',
        'duration',
        'quantity_prescribed',
        'quantity_dispensed',
        'instructions',
        'status',
        'created_at',
    ];

    protected $casts = [
        'quantity_prescribed' => 'integer',
        'quantity_dispensed' => 'integer',
        'created_at' => 'datetime',
    ];

    public function prescription(): BelongsTo
    {
        return $this->belongsTo(Prescription::class);
    }

    public function stockItem(): BelongsTo
    {
        return $this->belongsTo(StockItem::class, 'stock_item_id');
    }

    public function dispenseRecords(): HasMany
    {
        return $this->hasMany(DispenseRecord::class);
    }
}
