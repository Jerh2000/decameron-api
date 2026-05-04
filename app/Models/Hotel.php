<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Hotel extends Model
{
    protected $fillable = [
        'name',
        'address',
        'city',
        'nit',
        'total_rooms',
    ];

    /**
     * Un hotel tiene muchas configuraciones de habitación.
     */
    public function hotelRooms(): HasMany
    {
        return $this->hasMany(HotelRoom::class);
    }

    /**
     * Calcula cuántas habitaciones ya están configuradas en este hotel.
     */
    public function assignedRoomsCount(): int
    {
        return (int) $this->hotelRooms()->sum('quantity');
    }

    /**
     * Cuántas habitaciones quedan disponibles para asignar.
     */
    public function availableRooms(): int
    {
        return $this->total_rooms - $this->assignedRoomsCount();
    }
}
