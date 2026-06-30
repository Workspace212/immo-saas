<?php

namespace App\Enums;

enum OfferStatus: string
{
    case DRAFT = 'draft';
    case SUBMITTED = 'submitted';
    case ACCEPTED = 'accepted';
    case REJECTED = 'rejected';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::SUBMITTED => 'Soumise',
            self::ACCEPTED => 'Acceptée',
            self::REJECTED => 'Refusée',
            self::CANCELLED => 'Annulée',
        };
    }
}
