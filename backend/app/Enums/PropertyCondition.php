<?php

namespace App\Enums;

enum PropertyCondition: string
{
    case NEW = 'new';
    case EXCELLENT = 'excellent';
    case GOOD = 'good';
    case FAIR = 'fair';
    case NEEDS_WORK = 'needs_work';
    case RENOVATED = 'renovated';

    public function label(): string
    {
        return match ($this) {
            self::NEW => 'Neuf',
            self::EXCELLENT => 'Excellent',
            self::GOOD => 'Bon',
            self::FAIR => 'Correct',
            self::NEEDS_WORK => 'Travaux à prévoir',
            self::RENOVATED => 'Rénové',
        };
    }
}
