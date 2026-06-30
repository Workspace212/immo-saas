<?php

namespace App\Enums;

enum PartyType: string
{
    case OWNER = 'owner';
    case CLIENT = 'client';
    case GUARANTOR = 'guarantor';
    case WITNESS = 'witness';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::OWNER => 'Propriétaire',
            self::CLIENT => 'Client',
            self::GUARANTOR => 'Garant',
            self::WITNESS => 'Témoin',
            self::OTHER => 'Autre',
        };
    }
}
