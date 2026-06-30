<?php

namespace App\Enums;

enum MandateType: string
{
    case SIMPLE = 'simple';
    case EXCLUSIVE = 'exclusive';
    case SEMI_EXCLUSIVE = 'semi_exclusive';

    public function label(): string
    {
        return match ($this) {
            self::SIMPLE => 'Simple',
            self::EXCLUSIVE => 'Exclusif',
            self::SEMI_EXCLUSIVE => 'Semi-exclusif',
        };
    }
}
