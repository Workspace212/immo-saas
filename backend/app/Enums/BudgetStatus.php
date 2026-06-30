<?php

namespace App\Enums;

enum BudgetStatus: string
{
    case DRAFT = 'draft';
    case ACTIVE = 'active';
    case CLOSED = 'closed';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Brouillon',
            self::ACTIVE => 'Actif',
            self::CLOSED => 'Clôturé',
            self::CANCELLED => 'Annulé',
        };
    }
}
