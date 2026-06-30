<?php

namespace App\Enums;

enum DisbursementStatus: string
{
    case PENDING = 'pending';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case NOT_APPLICABLE = 'not_applicable';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PARTIALLY_PAID => 'Partiellement payé',
            self::PAID => 'Payé',
            self::NOT_APPLICABLE => 'Non applicable',
            self::CANCELLED => 'Annulé',
        };
    }
}
