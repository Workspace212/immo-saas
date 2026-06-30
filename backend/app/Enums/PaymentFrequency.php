<?php

namespace App\Enums;

enum PaymentFrequency: string
{
    case ONE_TIME = 'one_time';
    case MONTHLY = 'monthly';
    case QUARTERLY = 'quarterly';
    case YEARLY = 'yearly';

    public function label(): string
    {
        return match ($this) {
            self::ONE_TIME => 'Paiement unique',
            self::MONTHLY => 'Mensuel',
            self::QUARTERLY => 'Trimestriel',
            self::YEARLY => 'Annuel',
        };
    }
}
