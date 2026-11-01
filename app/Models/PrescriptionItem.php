<?php
namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class PrescriptionItem extends Model
{
    public $timestamps = false;
    protected $table   = 'prescription_items';
    protected $fillable = [
        'prescription_id','stock_item_id','drug_name','dose','route',
        'frequency','duration','quantity_prescribed','quantity_dispensed',
        'instructions','status',
    ];
}