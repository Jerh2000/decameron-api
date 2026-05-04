<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreHotelRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'room_type_id'     => ['required', 'integer', 'exists:room_types,id'],
            'accommodation_id' => ['required', 'integer', 'exists:accommodations,id'],
            'quantity'         => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'room_type_id.required'     => 'El tipo de habitación es obligatorio.',
            'room_type_id.exists'       => 'El tipo de habitación seleccionado no existe.',
            'accommodation_id.required' => 'La acomodación es obligatoria.',
            'accommodation_id.exists'   => 'La acomodación seleccionada no existe.',
            'quantity.required'         => 'La cantidad de habitaciones es obligatoria.',
            'quantity.min'              => 'La cantidad debe ser al menos 1.',
        ];
    }
}
