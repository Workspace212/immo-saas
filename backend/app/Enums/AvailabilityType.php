<?php

namespace App\Enums;

enum AvailabilityType: string
{
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case OCCUPIED = 'occupied';
    case MAINTENANCE = 'maintenance';
    case BLOCKED = 'blocked';
    case VISIT_SCHEDULED = 'visit_scheduled';

    public function label(): string
    {
        return match ($this) {
            self::AVAILABLE => 'Disponible',
            self::RESERVED => 'Réservé',
            self::OCCUPIED => 'Occupé',
            self::MAINTENANCE => 'Maintenance',
            self::BLOCKED => 'Bloqué',
            self::VISIT_SCHEDULED => 'Visite programmée',
        };
    }
}
