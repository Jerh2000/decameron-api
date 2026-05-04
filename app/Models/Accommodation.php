<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Accommodation extends Model
{
    protected $fillable = ['name'];

    /**
     * Una acomodación puede estar en muchas configuraciones de hotel.
     */
    public function hotelRooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }
}
