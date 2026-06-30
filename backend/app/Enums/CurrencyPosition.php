<?php

namespace App\Enums;

enum CurrencyPosition: string
{
    case BEFORE = 'before';
    case AFTER = 'after';

    public function label(): string
    {
        return match ($this) {
            self::BEFORE => 'Avant le montant',
            self::AFTER => 'Après le montant',
        };
    }
}
