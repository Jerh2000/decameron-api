<?php

namespace App\Services;

use App\Models\Accommodation;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Models\RoomType;
use App\Rules\RoomTypeAccommodationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class HotelService
{
    /**
     * Crea un nuevo hotel.
     *
     * @param array<string, mixed> $data
     */
    public function createHotel(array $data): Hotel
    {
        return DB::transaction(function () use ($data) {
            return Hotel::create($data);
        });
    }

    /**
     * Actualiza un hotel existente.
     * Valida que no se reduzca el total_rooms por debajo de las habitaciones ya configuradas.
     *
     * @param Hotel                $hotel
     * @param array<string, mixed> $data
     */
    public function updateHotel(Hotel $hotel, array $data): Hotel
    {
        return DB::transaction(function () use ($hotel, $data) {
            if (isset($data['total_rooms'])) {
                $assigned = $hotel->assignedRoomsCount();

                if ($data['total_rooms'] < $assigned) {
                    throw ValidationException::withMessages([
                        'total_rooms' => [
                            "No puedes reducir el total a {$data['total_rooms']}. " .
                            "Ya tienes {$assigned} habitaciones configuradas.",
                        ],
                    ]);
                }
            }

            $hotel->update($data);

            return $hotel->fresh();
        });
    }

    /**
     * Agrega una configuración de habitación a un hotel.
     * Aplica todas las reglas de negocio del dominio.
     *
     * @param Hotel                $hotel
     * @param array<string, mixed> $data
     */
    public function addRoomConfiguration(Hotel $hotel, array $data): HotelRoom
    {
        return DB::transaction(function () use ($hotel, $data) {
            $roomType      = RoomType::findOrFail($data['room_type_id']);
            $accommodation = Accommodation::findOrFail($data['accommodation_id']);

            // Regla 1: tipo + acomodación válidos según las reglas del negocio
            if (!RoomTypeAccommodationRule::isValid($roomType->name, $accommodation->name)) {
                $allowed = implode(', ', RoomTypeAccommodationRule::getAllowedFor($roomType->name));

                throw ValidationException::withMessages([
                    'accommodation_id' => [
                        "La acomodación '{$accommodation->name}' no es válida " .
                        "para habitaciones tipo '{$roomType->name}'. " .
                        "Permitidas: {$allowed}.",
                    ],
                ]);
            }

            // Regla 2: no duplicar tipo + acomodación en el mismo hotel
            $alreadyExists = $hotel->hotelRooms()
                ->where('room_type_id', $data['room_type_id'])
                ->where('accommodation_id', $data['accommodation_id'])
                ->exists();

            if ($alreadyExists) {
                throw ValidationException::withMessages([
                    'room_type_id' => [
                        "Este hotel ya tiene configurada la combinación " .
                        "'{$roomType->name} / {$accommodation->name}'.",
                    ],
                ]);
            }

            // Regla 3: no superar el total de habitaciones del hotel
            $assigned = $hotel->assignedRoomsCount();
            $newTotal = $assigned + $data['quantity'];

            if ($newTotal > $hotel->total_rooms) {
                $available = $hotel->availableRooms();

                throw ValidationException::withMessages([
                    'quantity' => [
                        "No hay espacio suficiente. " .
                        "El hotel tiene {$hotel->total_rooms} habitaciones en total, " .
                        "{$assigned} ya configuradas. " .
                        "Disponibles: {$available}.",
                    ],
                ]);
            }

            return HotelRoom::create([
                'hotel_id'         => $hotel->id,
                'room_type_id'     => $data['room_type_id'],
                'accommodation_id' => $data['accommodation_id'],
                'quantity'         => $data['quantity'],
            ]);
        });
    }

    /**
     * Actualiza la cantidad de una configuración de habitación existente.
     *
     * @param HotelRoom            $hotelRoom
     * @param array<string, mixed> $data
     */
    public function updateRoomConfiguration(HotelRoom $hotelRoom, array $data): HotelRoom
    {
        return DB::transaction(function () use ($hotelRoom, $data) {
            $hotel = $hotelRoom->hotel;

            // Total asignado excluyendo esta configuración + nueva cantidad
            $assignedExcludingThis = $hotel->hotelRooms()
                ->where('id', '!=', $hotelRoom->id)
                ->sum('quantity');

            $newTotal = $assignedExcludingThis + $data['quantity'];

            if ($newTotal > $hotel->total_rooms) {
                throw ValidationException::withMessages([
                    'quantity' => [
                        "La cantidad supera el máximo del hotel ({$hotel->total_rooms} habitaciones).",
                    ],
                ]);
            }

            $hotelRoom->update(['quantity' => $data['quantity']]);

            return $hotelRoom->fresh();
        });
    }

    /**
     * Elimina una configuración de habitación.
     */
    public function removeRoomConfiguration(HotelRoom $hotelRoom): void
    {
        $hotelRoom->delete();
    }
}
