<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    use HasFactory;
    protected $table = "card";

    public $timestamps = false;

    protected $primaryKey = "id";

    protected $fillable = [
        "name","location"
    ];
    public function parkingSessions()
    {
        return $this->hasMany(ParkingSession::class);
    }
}
