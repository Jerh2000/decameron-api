<?php

namespace App\Rules;

/**
 * Encapsula las reglas de negocio sobre qué acomodaciones
 * son válidas para cada tipo de habitación.
 *
 * Principio Open/Closed: si las reglas cambian, solo se modifica
 * la constante ALLOWED. Nada más en el sistema.
 */
class RoomTypeAccommodationRule
{
    /**
     * Mapa de reglas: tipo → acomodaciones permitidas.
     */
    private const ALLOWED = [
        'Estándar' => ['Sencilla', 'Doble'],
        'Junior'   => ['Triple', 'Cuádruple'],
        'Suite'    => ['Sencilla', 'Doble', 'Triple'],
    ];

    /**
     * Verifica si una combinación tipo + acomodación es válida.
     *
     * @param string $roomType      Nombre del tipo (ej: 'Estándar')
     * @param string $accommodation Nombre de la acomodación (ej: 'Doble')
     */
    public static function isValid(string $roomType, string $accommodation): bool
    {
        if (!isset(self::ALLOWED[$roomType])) {
            return false;
        }

        return in_array($accommodation, self::ALLOWED[$roomType]);
    }

    /**
     * Devuelve las acomodaciones permitidas para un tipo dado.
     * Útil para mensajes de error descriptivos.
     *
     * @param string $roomType
     * @return array<string>
     */
    public static function getAllowedFor(string $roomType): array
    {
        return self::ALLOWED[$roomType] ?? [];
    }

    /**
     * Devuelve todos los tipos de habitación válidos.
     *
     * @return array<string>
     */
    public static function validTypes(): array
    {
        return array_keys(self::ALLOWED);
    }
}
