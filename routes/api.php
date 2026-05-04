<?php

use App\Http\Controllers\CatalogController;
use App\Http\Controllers\HotelController;
use App\Http\Controllers\HotelRoomController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Catálogos (solo lectura — sin CRUD)
|--------------------------------------------------------------------------
*/
Route::prefix('catalogs')->group(function () {
    Route::get('room-types', [CatalogController::class, 'roomTypes']);
    Route::get('accommodations', [CatalogController::class, 'accommodations']);
    Route::get('room-types/{roomType}/accommodations', [CatalogController::class, 'accommodationsByRoomType']);
});

/*
|--------------------------------------------------------------------------
| Hoteles — CRUD completo
|--------------------------------------------------------------------------
| GET    /api/hotels          → index
| POST   /api/hotels          → store
| GET    /api/hotels/{hotel}  → show
| PUT    /api/hotels/{hotel}  → update
| DELETE /api/hotels/{hotel}  → destroy
*/
Route::apiResource('hotels', HotelController::class);

/*
|--------------------------------------------------------------------------
| Habitaciones de un hotel — rutas anidadas
|--------------------------------------------------------------------------
| GET    /api/hotels/{hotel}/rooms              → index
| POST   /api/hotels/{hotel}/rooms              → store
| PUT    /api/hotels/{hotel}/rooms/{hotelRoom}  → update
| DELETE /api/hotels/{hotel}/rooms/{hotelRoom}  → destroy
*/
Route::apiResource('hotels.rooms', HotelRoomController::class)
    ->parameters(['rooms' => 'hotelRoom'])
    ->only(['index', 'store', 'update', 'destroy']);
