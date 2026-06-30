<?php

namespace App\Enums;

enum PropertyStatus: string
{
    case DRAFT = 'draft';
    case AVAILABLE = 'available';
    case RESERVED = 'reserved';
    case SOLD = 'sold';
    case RENTED = 'rented';
    case ARCHIVED = 'archived';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::AVAILABLE => 'Disponible',
            self::RESERVED => 'Réservé',
            self::SOLD => 'Vendu',
            self::RENTED => 'Loué',
            self::ARCHIVED => 'Archivé',
        };
    }
}
