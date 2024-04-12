<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;
    protected $table = "parking_slots";

    public $timestamps = false;

    protected $primaryKey = "id";

    protected $fillable = [
        "slot_number","slot_code","is_available","parking_lot_id"
    ];
    public function parkingLot()
    {
        return $this->belongsTo(ParkingLot::class,'parking_lot_id');
    }
    public function parkingSessions()
    {
        return $this->hasMany(ParkingSession::class);
    }
}
