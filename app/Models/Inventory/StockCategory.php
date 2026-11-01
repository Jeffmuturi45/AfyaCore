<?php

namespace App\Models\Inventory;

use Illuminate\Database\Eloquent\Model;

class StockCategory extends Model
{
    protected $fillable = ['name', 'type', 'branch_type', 'description', 'is_active'];

    protected $casts = ['is_active' => 'boolean'];

    public function items()
    {
        return $this->hasMany(StockItem::class, 'category_id');
    }
}
