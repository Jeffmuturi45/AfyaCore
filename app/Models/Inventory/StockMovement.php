<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class StockMovement extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id', 'stock_item_id', 'movement_type', 'quantity',
        'quantity_before', 'quantity_after', 'reference_type', 'reference_id',
        'batch_number', 'expiry_date', 'unit_cost', 'performed_by', 'notes', 'performed_at',
    ];

    protected $casts = [
        'expiry_date' => 'date',
        'performed_at' => 'datetime',
    ];
}
