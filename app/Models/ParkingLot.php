<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingLot extends Model
{
    use HasFactory;
    protected $table = "parking_lots";

    public $timestamps = false;

    protected $primaryKey = "id";

    protected $fillable = [
        "name","address","price_per_hour","free_time_minutes"
    ];
    public function parkingSlots()
    {
        return $this->hasMany(ParkingSlot::class);
    }

}
