<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSession extends Model
{
    use HasFactory;
    protected $table = "parking_sessions";

    public $timestamps = false;

    protected $primaryKey = "id";

    protected $fillable = [
        "check_in_time","check_out_time","parking_slot_id","card_id"
    ];
    public function parkingSlots()
    {
        return $this->belongsTo(ParkingSlot::class,'parking_slot_id');
    }
    public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }
}
