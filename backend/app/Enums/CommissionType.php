<?php

namespace App\Enums;

enum CommissionType: string
{
    case PERCENT = 'percent';
    case FIXED = 'fixed';
    case ONE_MONTH_RENT = 'one_month_rent';

    public function label(): string
    {
        return match ($this) {
            self::PERCENT => 'Pourcentage',
            self::FIXED => 'Montant fixe',
            self::ONE_MONTH_RENT => 'Un mois de loyer',
        };
    }
}
