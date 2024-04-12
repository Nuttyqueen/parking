<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingReport extends Model
{
    use HasFactory;
    protected $table = "parking_reports";

    public $timestamps = false;

    protected $primaryKey = "id";

    protected $fillable = [
        "name","address","price_per_hour","free_time_minutes","parking_lot_id"
    ];
    public function parkingLots()
    {
        return $this->belongsTo(ParkingLot::class,'parking_lot_id');
    }

}
