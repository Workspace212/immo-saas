<?php

namespace App\Enums;

enum MandateStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::ACTIVE => 'Actif',
            self::EXPIRED => 'Expiré',
            self::CANCELLED => 'Annulé',
        };
    }
}
