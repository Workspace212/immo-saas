<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case PENDING = 'pending';
    case PARTIALLY_PAID = 'partially_paid';
    case PAID = 'paid';
    case OVERDUE = 'overdue';
    case CANCELLED = 'cancelled';
    case COMPLETED = 'completed';
    case REFUNDED = 'refunded';

    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'En attente',
            self::PARTIALLY_PAID => 'Partiellement payé',
            self::PAID => 'Payé',
            self::OVERDUE => 'En retard',
            self::CANCELLED => 'Annulé',
            self::COMPLETED => 'Complété',
            self::REFUNDED => 'Remboursé',
        };
    }
}
