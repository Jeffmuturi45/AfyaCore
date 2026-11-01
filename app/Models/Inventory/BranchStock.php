<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class BranchStock extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'branch_id', 'stock_item_id', 'quantity_on_hand', 'last_counted_at', 'last_movement_at',
    ];

    protected $casts = [
        'last_counted_at' => 'datetime',
        'last_movement_at' => 'datetime',
    ];

    public function isLow(): bool
    {
        return $this->quantity_on_hand <= $this->stockItem->reorder_level;
    }

    public function stockItem()
    {
        return $this->belongsTo(StockItem::class);
    }
}
