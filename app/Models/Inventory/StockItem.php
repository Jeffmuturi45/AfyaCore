<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class StockItem extends Model
{
    protected $fillable = [
        'uuid',
        'category_id',
        'name',
        'generic_name',
        'sku',
        'barcode',
        'unit_of_measure',
        'unit_price',
        'cost_price',
        'reorder_level',
        'is_prescription_only',
        'is_active',
        'description',
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'is_prescription_only' => 'boolean',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::creating(function ($item) {
            if (empty($item->uuid)) {
                $item->uuid = (string) Str::uuid();
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(StockCategory::class);
    }

    public function branchStock()
    {
        return $this->hasMany(BranchStock::class);
    }

    public function stockAtBranch(int $branchId): ?BranchStock
    {
        return $this->branchStock()->where('branch_id', $branchId)->first();
    }
}
