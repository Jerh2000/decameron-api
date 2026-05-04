<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotelRoomRequest;
use App\Http\Requests\UpdateHotelRoomRequest;
use App\Models\Hotel;
use App\Models\HotelRoom;
use App\Services\HotelService;
use Illuminate\Http\JsonResponse;

class HotelRoomController extends Controller
{
    public function __construct(
        private readonly HotelService $hotelService
    ) {}

    /**
     * GET /api/hotels/{hotel}/rooms
     * Lista las habitaciones configuradas de un hotel con métricas.
     */
    public function index(Hotel $hotel): JsonResponse
    {
        $rooms = $hotel->hotelRooms()->with([
            'roomType',
            'accommodation',
        ])->get();

        return response()->json([
            'success' => true,
            'data'    => $rooms,
            'meta'    => [
                'total_rooms'     => $hotel->total_rooms,
                'assigned_rooms'  => $hotel->assignedRoomsCount(),
                'available_rooms' => $hotel->availableRooms(),
            ],
        ]);
    }

    /**
     * POST /api/hotels/{hotel}/rooms
     * Agrega una configuración de habitación al hotel.
     */
    public function store(StoreHotelRoomRequest $request, Hotel $hotel): JsonResponse
    {
        $hotelRoom = $this->hotelService->addRoomConfiguration(
            $hotel,
            $request->validated()
        );

        $hotelRoom->load(['roomType', 'accommodation']);

        return response()->json([
            'success' => true,
            'message' => 'Configuración de habitación agregada exitosamente.',
            'data'    => $hotelRoom,
        ], 201);
    }

    /**
     * PUT /api/hotels/{hotel}/rooms/{hotelRoom}
     * Actualiza la cantidad de una configuración de habitación.
     */
    public function update(
        UpdateHotelRoomRequest $request,
        Hotel $hotel,
        HotelRoom $hotelRoom
    ): JsonResponse {
        if ($hotelRoom->hotel_id !== $hotel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Esta configuración no pertenece al hotel indicado.',
            ], 404);
        }

        $hotelRoom = $this->hotelService->updateRoomConfiguration(
            $hotelRoom,
            $request->validated()
        );

        $hotelRoom->load(['roomType', 'accommodation']);

        return response()->json([
            'success' => true,
            'message' => 'Configuración actualizada exitosamente.',
            'data'    => $hotelRoom,
        ]);
    }

    /**
     * DELETE /api/hotels/{hotel}/rooms/{hotelRoom}
     * Elimina una configuración de habitación.
     */
    public function destroy(Hotel $hotel, HotelRoom $hotelRoom): JsonResponse
    {
        if ($hotelRoom->hotel_id !== $hotel->id) {
            return response()->json([
                'success' => false,
                'message' => 'Esta configuración no pertenece al hotel indicado.',
            ], 404);
        }

        $this->hotelService->removeRoomConfiguration($hotelRoom);

        return response()->json([
            'success' => true,
            'message' => 'Configuración eliminada exitosamente.',
        ]);
    }
}
