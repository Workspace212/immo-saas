<?php

namespace App\Enums;

enum RenewalType: string
{
    case NEW_CONTRACT = 'new_contract';
    case EXTENSION = 'extension';
    case AUTOMATIC = 'automatic';

    public function label(): string
    {
        return match ($this) {
            self::NEW_CONTRACT => 'Nouveau contrat',
            self::EXTENSION => 'Prolongation',
            self::AUTOMATIC => 'Renouvellement automatique',
        };
    }
}
