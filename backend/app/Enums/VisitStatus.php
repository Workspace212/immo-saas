<?php

namespace App\Enums;

enum VisitStatus: string
{
    case SCHEDULED = 'scheduled';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case NO_SHOW = 'no_show';

    public function label(): string
    {
        return match ($this) {
            self::SCHEDULED => 'Programmée',
            self::COMPLETED => 'Terminée',
            self::CANCELLED => 'Annulée',
            self::NO_SHOW => 'Absent',
        };
    }
}
