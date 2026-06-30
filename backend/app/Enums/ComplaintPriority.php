<?php

namespace App\Enums;

enum ComplaintPriority: string
{
    case LOW = 'low';
    case NORMAL = 'normal';
    case URGENT = 'urgent';
    case CRITICAL = 'critical';

    public function label(): string
    {
        return match ($this) {
            self::LOW => 'Faible',
            self::NORMAL => 'Normale',
            self::URGENT => 'Urgente',
            self::CRITICAL => 'Critique',
        };
    }
}
