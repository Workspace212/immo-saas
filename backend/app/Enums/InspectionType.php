<?php

namespace App\Enums;

enum InspectionType: string
{
    case ENTRY = 'entry';
    case EXIT = 'exit';
    case INTERMEDIATE = 'intermediate';

    public function label(): string
    {
        return match ($this) {
            self::ENTRY => 'Entrée',
            self::EXIT => 'Sortie',
            self::INTERMEDIATE => 'Intermédiaire',
        };
    }
}
