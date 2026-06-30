<?php

namespace App\Enums;

enum ProviderStatus: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case BLACKLISTED = 'blacklisted';

    public function label(): string
    {
        return match ($this) {
            self::ACTIVE => 'Actif',
            self::INACTIVE => 'Inactif',
            self::BLACKLISTED => 'Liste noire',
        };
    }
}
