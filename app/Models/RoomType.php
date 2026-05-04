<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomType extends Model
{
    protected $fillable = ['name'];

    /**
     * Un tipo de habitación puede estar en muchas configuraciones de hotel.
     */
    public function hotelRooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }
}
