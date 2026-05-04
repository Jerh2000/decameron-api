<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreHotelRequest;
use App\Http\Requests\UpdateHotelRequest;
use App\Models\Hotel;
use App\Services\HotelService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class HotelController extends Controller
{
    public function __construct(
        private readonly HotelService $hotelService
    ) {}

    /**
     * GET /api/hotels
     * Lista todos los hoteles con sus habitaciones configuradas.
     */
    public function index(): JsonResponse
    {
        Log::info('Listando hoteles...');
        $hotels = Hotel::with([
            'hotelRooms.roomType',
            'hotelRooms.accommodation',
        ])->get();

        return response()->json([
            'success' => true,
            'data'    => $hotels,
        ]);
    }

    /**
     * POST /api/hotels
     * Crea un nuevo hotel.
     */
    public function store(StoreHotelRequest $request): JsonResponse
    {  
        Log::info('Creando hotel...');
        $hotel = $this->hotelService->createHotel($request->validated());
        
        return response()->json([
            'success' => true,
            'message' => 'Hotel creado exitosamente.',
            'data'    => $hotel,
        ], 201);
    }

    /**
     * GET /api/hotels/{hotel}
     * Muestra un hotel específico con todas sus habitaciones.
     */
    public function show(Hotel $hotel): JsonResponse
    {
        $hotel->load([
            'hotelRooms.roomType',
            'hotelRooms.accommodation',
        ]);

        return response()->json([
            'success' => true,
            'data'    => $hotel,
        ]);
    }

    /**
     * PUT/PATCH /api/hotels/{hotel}
     * Actualiza un hotel existente.
     */
    public function update(UpdateHotelRequest $request, Hotel $hotel): JsonResponse
    {
        $hotel = $this->hotelService->updateHotel($hotel, $request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Hotel actualizado exitosamente.',
            'data'    => $hotel,
        ]);
    }

    /**
     * DELETE /api/hotels/{hotel}
     * Elimina un hotel y sus configuraciones de habitación (cascade).
     */
    public function destroy(Hotel $hotel): JsonResponse
    {
        $hotel->delete();

        return response()->json([
            'success' => true,
            'message' => 'Hotel eliminado exitosamente.',
        ]);
    }
}
