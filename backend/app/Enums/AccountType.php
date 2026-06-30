<?php

namespace App\Enums;

enum AccountType: string
{
    case CASH = 'cash';
    case BANK = 'bank';
    case VIRTUAL = 'virtual';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::CASH => 'Espèces',
            self::BANK => 'Banque',
            self::VIRTUAL => 'Virtuel',
            self::OTHER => 'Autre',
        };
    }
}
