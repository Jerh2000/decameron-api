<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:150'],
            'address'     => ['required', 'string', 'max:200'],
            'city'        => ['required', 'string', 'max:100'],
            'nit'         => ['required', 'string', 'max:20', 'unique:hotels,nit'],
            'total_rooms' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required'        => 'El nombre del hotel es obligatorio.',
            'name.max'             => 'El nombre no puede superar 150 caracteres.',
            'address.required'     => 'La dirección es obligatoria.',
            'city.required'        => 'La ciudad es obligatoria.',
            'nit.required'         => 'El NIT es obligatorio.',
            'nit.unique'           => 'Ya existe un hotel registrado con este NIT.',
            'total_rooms.required' => 'El número total de habitaciones es obligatorio.',
            'total_rooms.integer'  => 'El total de habitaciones debe ser un número entero.',
            'total_rooms.min'      => 'El hotel debe tener al menos 1 habitación.',
        ];
    }
}
