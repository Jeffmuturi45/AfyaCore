<?php
namespace App\Models\Pharmacy;

use Illuminate\Database\Eloquent\Model;

class Prescription extends Model
{
    protected $table = 'prescriptions';
    protected $fillable = ['encounter_id','prescribed_by','status','notes'];

    public function items()
    {
        return $this->hasMany(PrescriptionItem::class);
    }
}