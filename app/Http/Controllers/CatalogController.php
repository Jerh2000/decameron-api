<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\RoomType;
use App\Rules\RoomTypeAccommodationRule;
use Illuminate\Http\JsonResponse;

class CatalogController extends Controller
{
    /**
     * GET /api/catalogs/room-types
     * Lista los tipos de habitación disponibles.
     */
    public function roomTypes(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => RoomType::all(),
        ]);
    }

    /**
     * GET /api/catalogs/accommodations
     * Lista todas las acomodaciones disponibles.
     */
    public function accommodations(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => Accommodation::all(),
        ]);
    }

    /**
     * GET /api/catalogs/room-types/{roomType}/accommodations
     * Devuelve las acomodaciones VÁLIDAS para un tipo de habitación específico.
     * Útil para que el frontend filtre opciones dinámicamente.
     */
    public function accommodationsByRoomType(RoomType $roomType): JsonResponse
    {
        $allowedNames   = RoomTypeAccommodationRule::getAllowedFor($roomType->name);
        $accommodations = Accommodation::whereIn('name', $allowedNames)->get();

        return response()->json([
            'success' => true,
            'data'    => $accommodations,
        ]);
    }
}
