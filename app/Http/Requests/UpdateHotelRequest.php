<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class UpdateHotelRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $hotel = $this->route('hotel');

        return [
            'name'        => ['sometimes', 'string', 'max:150'],
            'address'     => ['sometimes', 'string', 'max:200'],
            'city'        => ['sometimes', 'string', 'max:100'],
            'nit'         => [
                'sometimes',
                'string',
                'max:20',
                Rule::unique('hotels', 'nit')->ignore($hotel),
            ],
            'total_rooms' => ['sometimes', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'nit.unique'          => 'Ya existe otro hotel con este NIT.',
            'total_rooms.integer' => 'El total de habitaciones debe ser un número entero.',
            'total_rooms.min'     => 'El hotel debe tener al menos 1 habitación.',
        ];
    }
}
