<?php

namespace App\Enums;

enum PropertyPurpose: string
{
    case SALE = 'sale';
    case LONG_TERM_RENT = 'long_term_rent';
    case SHORT_TERM_RENT = 'short_term_rent';
    case PROPERTY_MANAGEMENT = 'property_management';
    case PROMOTION = 'promotion';

    public function label(): string
    {
        return match ($this) {
            self::SALE => 'Vente',
            self::LONG_TERM_RENT => 'Location longue durée',
            self::SHORT_TERM_RENT => 'Location courte durée',
            self::PROPERTY_MANAGEMENT => 'Gestion locative',
            self::PROMOTION => 'Promotion',
        };
    }
}
