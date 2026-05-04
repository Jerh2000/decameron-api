<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hotel_rooms', function (Blueprint $table) {
            $table->id();

            $table->foreignId('hotel_id')
                  ->constrained('hotels')
                  ->cascadeOnDelete();

            $table->foreignId('room_type_id')
                  ->constrained('room_types')
                  ->restrictOnDelete();

            $table->foreignId('accommodation_id')
                  ->constrained('accommodations')
                  ->restrictOnDelete();

            $table->unsignedInteger('quantity');

            // Garantiza unicidad: mismo hotel no puede tener la misma
            // combinación tipo+acomodación dos veces
            $table->unique(
                ['hotel_id', 'room_type_id', 'accommodation_id'],
                'unique_hotel_room_config'
            );

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hotel_rooms');
    }
};
