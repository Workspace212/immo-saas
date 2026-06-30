<?php

namespace App\Enums;

enum ReminderType: string
{
    case BEFORE_15_MINUTES = 'before_15_minutes';
    case BEFORE_1_HOUR = 'before_1_hour';
    case BEFORE_1_DAY = 'before_1_day';
    case CUSTOM = 'custom';

    public function label(): string
    {
        return match ($this) {
            self::BEFORE_15_MINUTES => '15 minutes avant',
            self::BEFORE_1_HOUR => '1 heure avant',
            self::BEFORE_1_DAY => '1 jour avant',
            self::CUSTOM => 'Personnalisé',
        };
    }
}
